<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('hash')->nullable()->change();
            $table->string('status')->default('ready')->after('reference_count');
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('hash')->nullable(false)->change();
            $table->dropColumn('status');
        });
    }
};
