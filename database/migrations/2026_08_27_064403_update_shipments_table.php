<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table): void {
            $table->foreignId('order_id')
                ->nullable()
                ->after('id')
                ->constrained('orders')
                ->nullOnDelete();

            $table->string('remote_shipment_id')
                ->nullable()
                ->index();

            $table->string('invoice_number')
                ->nullable()
                ->index();

            $table->string('api_followup_phone')
                ->nullable();

            $table->decimal('collected_amount', 12, 2)
                ->default(0);

            $table->unsignedInteger('number_of_items')
                ->default(0);

            $table->boolean('can_open')
                ->default(true);

            $table->timestamp('shipped_at')
                ->nullable();

            $table->timestamp('delivered_at')
                ->nullable();

            $table->timestamp('returned_at')
                ->nullable();

            $table->timestamp('last_synced_at')
                ->nullable();

            $table->index(
                ['order_id', 'status'],
                'shipments_order_id_status_index'
            );

            $table->index(
                ['created_at', 'status'],
                'shipments_created_at_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table): void {
            $table->dropForeign(['order_id']);

            $table->dropIndex('shipments_remote_shipment_id_index');
            $table->dropIndex('shipments_invoice_number_index');
            $table->dropIndex('shipments_order_id_status_index');
            $table->dropIndex('shipments_created_at_status_index');

            $table->dropColumn([
                'order_id',
                'remote_shipment_id',
                'invoice_number',
                'api_followup_phone',
                'collected_amount',
                'number_of_items',
                'can_open',
                'shipped_at',
                'delivered_at',
                'returned_at',
                'last_synced_at',
            ]);
        });
    }
};