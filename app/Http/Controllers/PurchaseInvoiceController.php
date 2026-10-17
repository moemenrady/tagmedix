<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\InventoryStock;
use App\Modules\Inventory\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    /**
     * Display a listing of purchase invoices.
     */
    public function index(Request $request)
    {
        $query = PurchaseInvoice::with(['supplier', 'creator'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('supplier_invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->payment_status, fn($q, $ps) => $q->where('payment_status', $ps))
            ->when($request->date_from, fn($q, $df) => $q->whereDate('invoice_date', '>=', $df))
            ->when($request->date_to, fn($q, $dt) => $q->whereDate('invoice_date', '<=', $dt));

        $stats = [
            'total_invoices_count' => (clone $query)->count(),
            'total_amount' => (clone $query)->where('status', '!=', 'cancelled')->sum('total_amount'),
            'total_paid' => (clone $query)->where('status', '!=', 'cancelled')->sum('paid_amount'),
            'total_remaining' => (clone $query)->where('status', '!=', 'cancelled')->sum('remaining_amount'),
        ];

        $invoices = $query->latest('invoice_date')->latest('id')->paginate(15)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('pages.purchase_invoices.index', compact('invoices', 'stats', 'suppliers'));
    }

    /**
     * Show the form for creating a new purchase invoice.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $inventoryItems = InventoryItem::where('is_active', true)
            ->with('variants')
            ->orderBy('name')
            ->get();

        $nextInvoiceNumber = PurchaseInvoice::generateInvoiceNumber();

        return view('pages.purchase_invoices.create', compact('suppliers', 'inventoryItems', 'nextInvoiceNumber'));
    }

    /**
     * Store a newly created purchase invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'status' => 'required|in:draft,received',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.inventory_item_variant_id' => 'nullable|exists:inventory_item_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.unit_type' => 'nullable|string|max:50',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $invoiceNumber = PurchaseInvoice::generateInvoiceNumber();

            $subtotal = 0;
            foreach ($validated['items'] as $itemData) {
                $subtotal += ($itemData['quantity'] * $itemData['unit_price']);
            }

            $discount = (float)($validated['discount'] ?? 0);
            $tax = (float)($validated['tax'] ?? 0);
            $shippingCost = (float)($validated['shipping_cost'] ?? 0);
            $totalAmount = max(0, $subtotal - $discount + $tax + $shippingCost);
            $paidAmount = min($totalAmount, (float)($validated['paid_amount'] ?? 0));
            $remainingAmount = max(0, $totalAmount - $paidAmount);

            $paymentStatus = 'unpaid';
            if ($paidAmount >= $totalAmount && $totalAmount > 0) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            $invoice = PurchaseInvoice::create([
                'invoice_number' => $invoiceNumber,
                'supplier_id' => $validated['supplier_id'],
                'supplier_invoice_number' => $validated['supplier_invoice_number'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'status' => $validated['status'],
                'payment_status' => $paymentStatus,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'received_at' => $validated['status'] === 'received' ? now() : null,
            ]);

            // Save items
            foreach ($validated['items'] as $itemData) {
                $item = InventoryItem::find($itemData['inventory_item_id']);
                $totalPrice = $itemData['quantity'] * $itemData['unit_price'];

                $invoiceItem = PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'inventory_item_variant_id' => $itemData['inventory_item_variant_id'] ?? null,
                    'item_name' => $item->name,
                    'quantity' => $itemData['quantity'],
                    'unit_type' => $itemData['unit_type'] ?? $item->unit_type,
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $totalPrice,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // If invoice is received, update inventory immediately
                if ($invoice->status === 'received') {
                    $this->receiveItemToStock($invoice, $invoiceItem);
                }
            }

            // If there's an initial payment recorded
            if ($paidAmount > 0) {
                SupplierPayment::create([
                    'payment_number' => SupplierPayment::generatePaymentNumber(),
                    'supplier_id' => $invoice->supplier_id,
                    'purchase_invoice_id' => $invoice->id,
                    'amount' => $paidAmount,
                    'payment_date' => $invoice->invoice_date,
                    'payment_method' => $invoice->payment_method ?? 'cash',
                    'notes' => 'دفعة مقدمة مسددة مع الفاتورة',
                    'created_by' => auth()->id(),
                ]);
            }

            return redirect()->route('purchase-invoices.show', $invoice)
                ->with('success', 'تم إنشاء فاتورة الشراء بنجاح.');
        });
    }

    /**
     * Display the specified purchase invoice.
     */
    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load([
            'supplier',
            'creator',
            'items.inventoryItem',
            'items.variant',
            'payments.creator'
        ]);

        return view('pages.purchase_invoices.show', compact('purchaseInvoice'));
    }

    /**
     * Approve and receive invoice items into the inventory.
     */
    public function receive(PurchaseInvoice $purchaseInvoice)
    {
        if ($purchaseInvoice->status === 'received') {
            return back()->with('info', 'تم استلام بنود هذه الفاتورة في المخزن بالفعل مسبقاً.');
        }

        if ($purchaseInvoice->status === 'cancelled') {
            return back()->with('error', 'لا يمكن استلام فاتورة ملغاة.');
        }

        DB::transaction(function () use ($purchaseInvoice) {
            $purchaseInvoice->load('items');

            foreach ($purchaseInvoice->items as $item) {
                $this->receiveItemToStock($purchaseInvoice, $item);
            }

            $purchaseInvoice->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
        });

        return back()->with('success', 'تم اعتماد الفاتورة واستلام جميع الأصناف في المخزون بنجاح.');
    }

    /**
     * Helper to stock-in an item and log the transaction.
     */
    private function receiveItemToStock(PurchaseInvoice $invoice, PurchaseInvoiceItem $item): void
    {
        $stock = InventoryStock::firstOrNew([
            'inventory_item_id' => $item->inventory_item_id,
            'inventory_item_variant_id' => $item->inventory_item_variant_id,
        ]);

        $prevQuantity = $stock->exists ? (float) $stock->quantity : 0;
        $newQuantity = $prevQuantity + (float) $item->quantity;

        $stock->quantity = $newQuantity;
        $stock->unit_type = $item->unit_type;
        $stock->unit_cost = $item->unit_price;
        $stock->total_cost = $newQuantity * (float) $item->unit_price;
        $stock->save();

        // Calculate balance after across stocks
        $balanceAfter = InventoryStock::where('inventory_item_id', $item->inventory_item_id)
            ->where('inventory_item_variant_id', $item->inventory_item_variant_id)
            ->sum('quantity');

        // Create transaction log
        InventoryTransaction::create([
            'inventory_item_id' => $item->inventory_item_id,
            'inventory_item_variant_id' => $item->inventory_item_variant_id,
            'inventory_unit_id' => null,
            'type' => 'receipt',
            'quantity' => $item->quantity,
            'unit_type' => $item->unit_type,
            'unit_cost' => $item->unit_price,
            'total_cost' => $item->total_price,
            'balance_after' => $balanceAfter,
            'reference_type' => PurchaseInvoice::class,
            'reference_id' => $invoice->id,
            'notes' => 'وارد مشتريات بموجب فاتورة شراء رقم: ' . $invoice->invoice_number,
            'created_by' => auth()->id(),
            'transaction_date' => now(),
        ]);
    }

    /**
     * Add a payment against this invoice.
     */
    public function addPayment(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . ($purchaseInvoice->remaining_amount + 0.01),
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment = SupplierPayment::create([
            'payment_number' => SupplierPayment::generatePaymentNumber(),
            'supplier_id' => $purchaseInvoice->supplier_id,
            'purchase_invoice_id' => $purchaseInvoice->id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $purchaseInvoice->recalculateTotals();

        return back()->with('success', 'تم سداد الدفعة بنجاح وتحديث الرصيد.');
    }
}
