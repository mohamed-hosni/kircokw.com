<?php

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
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('payment_type')->default(0);
            $table->float('price')->default(0);
            $table->integer('garages')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->string('title')->nullable();
            $table->string('address')->nullable();
            $table->string('location')->nullable();
            $table->float('area')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('payment_type');
            $table->dropColumn('price');
            $table->dropColumn('garages');
            $table->dropColumn('bathrooms');
            $table->dropColumn('bedrooms');
            $table->dropColumn('title');
            $table->dropColumn('address');
            $table->dropColumn('location');
            $table->dropColumn('area');
        });
    }
};
