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
        Schema::create('attendees2', function (Blueprint $table) {
            $table->id();

    $table->string('no')->nullable(); // ลำดับ
    $table->date('register_date')->nullable();      // วันที่สมัคร
            // personal
    $table->string('first_name_th')->nullable();
    $table->string('last_name_th')->nullable();
    $table->string('first_name_en')->nullable();
    $table->string('last_name_en')->nullable();

    $table->string('email')->nullable();
    $table->string('phone')->nullable();

    // organization
    $table->string('organization')->nullable(); //สังกัด
    $table->string('academic_position')->nullable(); //ตำแหน่งวิชาการ
    $table->string('admin_position')->nullable(); //ตำแหน่งบริหาร

    // location
    $table->boolean('province_type_1')->nullable(); //ประเภท กรุงเทพฯ
    $table->boolean('province_type_2')->nullable(); //ประเภท ต่างจังหวัด
    $table->string('province')->nullable(); //เขต/จังหวัด

    // travel
    $table->string('travel_from_province')->nullable(); // วิธีการเดินทาง

    // food
    $table->string('food_type')->nullable();                 // ประเภทอาหาร
    $table->string('food_allergy')->nullable();              // แพ้อาหาร
    $table->string('food_other_constraints')->nullable();    // ข้อจำกัดอื่น ๆ

    // T - V (กิจกรรมที่เข้าร่วม)
    $table->boolean('activity_workshop')->nullable();        // Workshop
    $table->boolean('activity_conference')->nullable();      // Conference
    $table->boolean('activity_excursion')->nullable();       // Excursion

    // W - Y (ประเภทการนำเสนอ)
    $table->boolean('presentation_conference')->nullable();  // Conference
    $table->boolean('presentation_oral')->nullable();        // Oral
    $table->boolean('presentation_poster')->nullable();      // Poster

    // status
    $table->string('register_status')->nullable();
    $table->string('attendance_status')->nullable();

    // note
    $table->text('note')->nullable();
    $table->text('admin_note')->nullable();

    // misc
    $table->string('care')->nullable();
    $table->string('qr_code')->nullable();

    // date

    $table->date('register_date1')->nullable();     // ที่คุณขอเพิ่ม
    $table->date('register_date2')->nullable();     // ที่คุณขอเพิ่ม


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendees2');
    }
};
