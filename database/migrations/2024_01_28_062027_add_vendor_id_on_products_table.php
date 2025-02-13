<?php

use App\Models\Vendor;
use Lunar\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->prefix . 'products', function (Blueprint $table) {
            $table->foreignIdFor(Vendor::class)->after("product_type_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix . 'products', function (Blueprint $table) {
            $table->dropForeignIdFor(Vendor::class);
        });
    }
};