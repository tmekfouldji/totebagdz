<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('price');
            $table->unsignedInteger('compare_at_price')->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('free_shipping')->default(false);
            $table->boolean('track_inventory')->default(true);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('active')->default(true);
            $table->string('image');
            $table->timestamps();
            $table->unique(['product_id', 'key']);
        });

        Schema::create('wilayas', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name');
            $table->string('name_ar');
            $table->timestamps();
        });

        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('wilaya_id');
            $table->string('name');
            $table->string('name_ar');
            $table->unsignedInteger('source_id')->nullable()->unique();
            $table->timestamps();
            $table->foreign('wilaya_id')->references('id')->on('wilayas')->cascadeOnDelete();
            $table->index(['wilaya_id', 'name']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number')->unique();
            $table->string('status')->default('pending')->index();
            $table->string('payment_status')->default('pending');
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_variant_id')->constrained();
            $table->string('product_name');
            $table->string('color');
            $table->unsignedTinyInteger('quantity');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('shipping_fee');
            $table->unsignedInteger('total');
            $table->string('customer_name');
            $table->string('phone', 20)->index();
            $table->unsignedTinyInteger('wilaya_id');
            $table->string('wilaya_name');
            $table->string('commune');
            $table->string('address');
            $table->string('notes')->nullable();
            $table->string('delivery_type')->default('home');
            $table->string('payment_method')->default('cash_on_delivery');
            $table->boolean('inventory_tracked')->default(true);
            $table->boolean('stock_restored')->default(false);
            $table->string('noest_tracking')->nullable()->index();
            $table->timestamp('noest_dispatched_at')->nullable();
            $table->json('noest_response')->nullable();
            $table->timestamps();
            $table->foreign('wilaya_id')->references('id')->on('wilayas');
        });

        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_id');
            $table->string('action');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_histories');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('communes');
        Schema::dropIfExists('wilayas');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
