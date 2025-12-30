<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendee;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class AttendeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = Excel::toCollection(null, storage_path('app/import/attendees.xlsx'))[0];

        // ข้าม header
        $rows->skip(1)->each(function ($row) {

            Attendee::create([
                'first_name_th' => $row[0],
                'last_name_th' => $row[1] ?? null,
                'first_name_en' => $row[2],
                'last_name_en' => $row[3],
                'email' => $row[4],
                'phone' => $row[5],
                'organization' => $row[6],
                'academic_position' => $row[7],
                'admin_position' => $row[8],
                'province_type' => $row[9],
                'bangkok_zone' => $row[10],
                'province' => $row[11],
                'travel_from_province' => $row[12],
                'travel_from_hotel' => $row[13],
                'food_type' => $row[14],
                'food_allergy' => $row[15],
                'activity' => $row[16],
                'presentation_type' => $row[17],
                'qr_code' => $row[18],
                'register_date' => $row[19],
                'status' => $row[20] ?? 'waiting',
            ]);
        });
    }
}
