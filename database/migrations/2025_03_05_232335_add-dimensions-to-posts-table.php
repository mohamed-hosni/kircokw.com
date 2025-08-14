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
            $table->string('year_built')->default(0);
            $table->string('type')->default(0);
            $table->string('master_bedroom')->nullable();
            $table->string('bedroom_two')->nullable();
            $table->string('other_room')->nullable();
            $table->string('living_room')->nullable();
            $table->string('kitchen')->nullable();
            $table->string('dining_room')->nullable();
            $table->integer('half_baths')->nullable();
            $table->integer('full_baths')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('year_built');
            $table->dropColumn('type');
            $table->dropColumn('master_bedroom');
            $table->dropColumn('bedroom_two');
            $table->dropColumn('other_room');
            $table->dropColumn('living_room');
            $table->dropColumn('kitchen');
            $table->dropColumn('dining_room');
            $table->dropColumn('half_baths');
            $table->dropColumn('full_baths');        
        });
    }
};
