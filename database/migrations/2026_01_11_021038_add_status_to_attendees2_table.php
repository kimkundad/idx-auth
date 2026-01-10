<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendees2', function (Blueprint $table) {
            $table->string('status')
                ->default('waiting')
                ->after('register_date2'); // จะ after อะไรเปลี่ยนได้
        });
    }

    public function down(): void
    {
        Schema::table('attendees2', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
