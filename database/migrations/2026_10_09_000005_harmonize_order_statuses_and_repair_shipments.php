<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Link any shipments with null order_id to their orders via remote_order_id
        if (Schema::hasTable('shipments') && Schema::hasTable('orders')) {
            $unlinkedShipments = DB::table('shipments')
                ->whereNull('order_id')
                ->whereNotNull('remote_order_id')
                ->get();

            foreach ($unlinkedShipments as $shipment) {
                $order = DB::table('orders')->where('order_number', $shipment->remote_order_id)->first();
                if ($order) {
                    DB::table('shipments')->where('id', $shipment->id)->update(['order_id' => $order->id]);
                }
            }

            // 2. Repair order statuses: Any order that has an associated shipment should have its status restored
            $shipments = DB::table('shipments')->get();
            foreach ($shipments as $shipment) {
                $order = null;
                if ($shipment->order_id) {
                    $order = DB::table('orders')->where('id', $shipment->order_id)->first();
                }
                if (!$order && $shipment->remote_order_id) {
                    $order = DB::table('orders')->where('order_number', $shipment->remote_order_id)->first();
                }

                if ($order) {
                    $targetStatus = 'shipped';
                    $statusText = mb_strtolower((string) ($shipment->status_text ?? $shipment->status ?? ''));

                    if (str_contains($statusText, 'تسليم') || str_contains($statusText, 'delivered') || in_array($shipment->status, ['3', 'delivered', 'DELIVERED'])) {
                        $targetStatus = 'delivered';
                    } elseif (str_contains($statusText, 'مرتجع') || str_contains($statusText, 'ملغي') || in_array($shipment->status, ['4', 'canceled', 'cancelled', 'CANCELED', 'محذوفة'])) {
                        $targetStatus = 'canceled';
                    }

                    // If order status is empty, invalid, or reverted to new/cutting/sewing despite having a shipment
                    if (empty($order->status) || in_array($order->status, ['', 'new', 'confirmed', 'cutting', 'sewing', 'ironing', 'finishing'])) {
                        DB::table('orders')->where('id', $order->id)->update(['status' => $targetStatus]);
                    }
                }
            }

            // 3. Migrate legacy unused statuses: finishing -> ready-to-ship, confirmed -> new, ironing -> sewing
            DB::table('orders')->where('status', 'finishing')->update(['status' => 'ready-to-ship']);
            DB::table('orders')->where('status', 'confirmed')->update(['status' => 'new']);
            DB::table('orders')->where('status', 'ironing')->update(['status' => 'sewing']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reversal needed for data harmonization
    }
};
