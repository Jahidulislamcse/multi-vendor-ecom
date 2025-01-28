<?php

use App\Models\Livestream;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('livestream_product', function (Blueprint $table) {
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(Livestream::class);

            $table->unique(['product_id', 'livestream_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestream_product');
    }
};
