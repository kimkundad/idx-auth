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
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();

            $table->string('first_name_th')->nullable();
            $table->string('last_name_th')->nullable();
            $table->string('first_name_en')->nullable();
            $table->string('last_name_en')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('organization')->nullable();
            $table->string('academic_position')->nullable();
            $table->string('admin_position')->nullable();

            $table->string('province_type')->nullable();
            $table->string('bangkok_zone')->nullable();
            $table->string('province')->nullable();

            $table->string('travel_from_province')->nullable();
            $table->string('travel_from_hotel')->nullable();

            $table->string('food_type')->nullable();
            $table->string('food_allergy')->nullable();

            $table->string('activity')->nullable();
            $table->string('presentation_type')->nullable();

            $table->string('qr_code')->nullable();
            $table->date('register_date')->nullable();
            $table->string('status')->default('waiting');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
