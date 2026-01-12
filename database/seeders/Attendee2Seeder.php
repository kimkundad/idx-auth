<?php

namespace Database\Seeders;

use App\Models\Attendee2;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Attendee2Seeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/import/EnT2026_for IDX_10 Jan.xlsx');

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('ข้อมูลผู้สมัคร');

        // จากไฟล์จริง ข้อมูลเริ่มที่แถว 6
        $startRow = 6;
        $highestRow = $sheet->getHighestRow();

        for ($row = $startRow; $row <= $highestRow; $row++) {

            // ถ้าไม่มีชื่อ (กันแถวว่าง)
            if (!$sheet->getCell("C{$row}")->getValue()) {
                continue;
            }

            Attendee2::create([
                // A - B
                'no' => (string) $sheet->getCell("A{$row}")->getValue(),
                'register_date' => $this->parseDate($sheet->getCell("B{$row}")->getValue()),

                // C - F
                'first_name_th' => $sheet->getCell("C{$row}")->getValue(),
                'last_name_th'  => $sheet->getCell("D{$row}")->getValue(),
                'first_name_en' => $sheet->getCell("E{$row}")->getValue(),
                'last_name_en'  => $sheet->getCell("F{$row}")->getValue(),

                // G - H
                'email' => $sheet->getCell("G{$row}")->getValue(),
                'phone' => $sheet->getCell("H{$row}")->getValue(),

                // I - K
                'organization'        => $sheet->getCell("I{$row}")->getValue(),
                'academic_position'   => $sheet->getCell("J{$row}")->getValue(),
                'admin_position'      => $sheet->getCell("K{$row}")->getValue(),

                // L - N
                'province_type_1' => $this->toBool($sheet->getCell("L{$row}")->getValue()),
                'province_type_2' => $this->toBool($sheet->getCell("M{$row}")->getValue()),
                'province'        => $sheet->getCell("N{$row}")->getValue(),

                // O
                'travel_from_province' => $sheet->getCell("O{$row}")->getValue(),

                // P - R (อาหาร)
                'food_type'              => $sheet->getCell("P{$row}")->getValue(),
                'food_allergy'           => $sheet->getCell("Q{$row}")->getValue(),
                'food_other_constraints' => $sheet->getCell("R{$row}")->getValue(),

                // S - U (กิจกรรมที่เข้าร่วม)
                'activity_workshop'   => $this->toBool($sheet->getCell("S{$row}")->getValue()),
                'activity_conference' => $this->toBool($sheet->getCell("T{$row}")->getValue()),
                'activity_excursion'  => $this->toBool($sheet->getCell("U{$row}")->getValue()),

                // V - X (ประเภทการนำเสนอ)
                'presentation_conference' => $this->toBool($sheet->getCell("V{$row}")->getValue()),
                'presentation_oral'        => $this->toBool($sheet->getCell("W{$row}")->getValue()),
                'presentation_poster'      => $this->toBool($sheet->getCell("X{$row}")->getValue()),


                'qr_code' => $sheet->getCell("AF{$row}")->getValue(),

                // extra dates (ยังไม่มีในไฟล์)
                'register_date1' => null,
                'register_date2' => null,
            ]);
        }
    }

    /**
     * แปลง TRUE/FALSE / 1/0 / yes/no → boolean
     */
    private function toBool($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));

        return match ($value) {
            'true', '1', 'yes'  => true,
            'false', '0', 'no'  => false,
            default             => null,
        };
    }

    /**
     * แปลงวันที่จาก Excel (รองรับ พ.ศ.)
     */
    private function parseDate($value): ?string
    {
        if (!$value) {
            return null;
        }

        // Excel numeric date
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                ->format('Y-m-d');
        }

        // string date (พ.ศ.)
        try {
            $year = (int) substr($value, 0, 4);
            if ($year > 2500) {
                $value = ($year - 543) . substr($value, 4);
            }

            return date('Y-m-d', strtotime($value));
        } catch (\Throwable $e) {
            return null;
        }
    }
}
