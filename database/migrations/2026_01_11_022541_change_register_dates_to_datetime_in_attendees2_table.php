<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendees2', function (Blueprint $table) {
            $table->dateTime('register_date1')->nullable()->change();
            $table->dateTime('register_date2')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendees2', function (Blueprint $table) {
            $table->date('register_date1')->nullable()->change();
            $table->date('register_date2')->nullable()->change();
        });
    }
};
