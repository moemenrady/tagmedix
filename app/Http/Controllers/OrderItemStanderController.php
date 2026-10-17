<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\PantType;
use App\Models\Size;
use App\Models\SleeveType;
use App\Models\Style;
use App\Models\Type;
use App\Models\OrderItemStander;
use App\Services\OrderItemStanderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderItemStanderController extends Controller
{
    public function __construct(
        protected OrderItemStanderService $service
    ) {}

    /**
     * Display the order item standards page.
     */
    public function index(): View
    {
        $orderItemStanders = $this->service->index();

        $pantTypes = PantType::query()
            ->orderBy('name')
            ->get();

        $types = Type::query()
            ->orderBy('name')
            ->get();

        $styles = Style::query()
            ->orderBy('name')
            ->get();

        $colors = Color::query()
            ->orderBy('name')
            ->get();

        $sizes = Size::query()
            ->orderBy('name')
            ->get();

        $sleeveTypes = SleeveType::query()
            ->orderBy('name')
            ->get();

        return view(
            'pages.settings.order_item_stander.index',
            compact(
                'orderItemStanders',
                'pantTypes',
                'types',
                'styles',
                'colors',
                'sizes',
                'sleeveTypes'
            )
        );
    }

    /**
     * Store a new order item standard.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pant_type_id' => ['nullable', 'integer', 'exists:pant_types,id'],
            'type_id' => ['nullable', 'integer', 'exists:types,id'],
            'style_id' => ['nullable', 'integer', 'exists:styles,id'],
            'color_id' => ['nullable', 'integer', 'exists:colors,id'],
            'size_id' => ['nullable', 'integer', 'exists:sizes,id'],
            'sleeve_type_id' => ['nullable', 'integer', 'exists:sleeve_types,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $this->service->create($validated);

        return redirect()
            ->route('settings.order-item-stander.index')
            ->with('success', 'تم إضافة الستاندر بنجاح.');
    }

    /**
     * Show a specific order item standard.
     */
    public function show(int $id): View
    {
        $orderItemStander = $this->service->find($id);

        $orderItemStanders = $this->service->index();

        $pantTypes = PantType::query()
            ->orderBy('name')
            ->get();

        $types = Type::query()
            ->orderBy('name')
            ->get();

        $styles = Style::query()
            ->orderBy('name')
            ->get();

        $colors = Color::query()
            ->orderBy('name')
            ->get();

        $sizes = Size::query()
            ->orderBy('name')
            ->get();

        $sleeveTypes = SleeveType::query()
            ->orderBy('name')
            ->get();

        return view(
            'pages.settings.order_item_stander.index',
            compact(
                'orderItemStander',
                'orderItemStanders',
                'pantTypes',
                'types',
                'styles',
                'colors',
                'sizes',
                'sleeveTypes'
            )
        );
    }

    /**
     * Update an order item standard.
     */
    public function update(
        Request $request,
        int $id
    ): RedirectResponse {
        $orderItemStander = $this->service->find($id);

        $validated = $request->validate([
            'pant_type_id' => ['nullable', 'integer', 'exists:pant_types,id'],
            'type_id' => ['nullable', 'integer', 'exists:types,id'],
            'style_id' => ['nullable', 'integer', 'exists:styles,id'],
            'color_id' => ['nullable', 'integer', 'exists:colors,id'],
            'size_id' => ['nullable', 'integer', 'exists:sizes,id'],
            'sleeve_type_id' => ['nullable', 'integer', 'exists:sleeve_types,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $this->service->update(
            $orderItemStander,
            $validated
        );

        return redirect()
            ->route('settings.order-item-stander.index')
            ->with('success', 'تم تحديث الستاندر بنجاح.');
    }

    /**
     * Delete an order item standard.
     */
    public function destroy(int $id): RedirectResponse
    {
        $orderItemStander = $this->service->find($id);

        $this->service->delete($orderItemStander);

        return redirect()
            ->route('settings.order-item-stander.index')
            ->with('success', 'تم حذف الستاندر بنجاح.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $orderItemStander = $this->service->find($id);

        $this->service->toggleStatus($orderItemStander);

        return redirect()
            ->route('settings.order-item-stander.index')
            ->with('success', 'تم تحديث حالة الستاندر بنجاح.');
    }
}
