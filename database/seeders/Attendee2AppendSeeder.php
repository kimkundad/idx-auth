<?php

namespace Database\Seeders;

use App\Models\Attendee2;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Attendee2AppendSeeder extends Seeder
{
    public function run(): void
    {
        $file = storage_path('app/import/AddEnT2026_for IDX_13 Jan.xlsx');
        if (!file_exists($file)) {
            $this->command?->error("File not found: {$file}");
            return;
        }

        $sheet = IOFactory::load($file)->getSheet(0);

        // ✅ ถ้า no เป็น integer แล้ว ตัวนี้จะชัวร์
        $lastNo = (int) (Attendee2::max('no') ?? 0);

        // จากไฟล์คุณ: header อยู่แถว 4-5, data เริ่มแถว 6
        $startRow = 6;
        $endRow = $sheet->getHighestRow();

        $inserted = 0;
        $skipped = 0;
        $updated = 0;

        for ($r = $startRow; $r <= $endRow; $r++) {

            // ---- mapping ตามตำแหน่งคอลัมน์ในไฟล์ ----
            // A: no, B: register_date, C: first_th, D: last_th, E: first_en, F: last_en
            // G: email, H: phone, I: organization, J: academic_position, K: admin_position
            // L: กรุงเทพฯ (1/0), M: ตจว (1/0), N: province, O: travel_from_province
            // P: food_type, Q: food_allergy, R: food_other_constraints
            // S: activity_workshop, T: activity_conference, U: activity_excursion
            // V: presentation_conference, W: presentation_oral, X: presentation_poster
            // AH(34): qr_code  (จากไฟล์ล่าสุดคุณ QR อยู่คอลัมน์ 34)

            $noFromFile = $sheet->getCell("A{$r}")->getValue();
            $registerDate = $sheet->getCell("B{$r}")->getFormattedValue();

            $firstTh = trim((string) $sheet->getCell("C{$r}")->getValue());
            $lastTh  = trim((string) $sheet->getCell("D{$r}")->getValue());
            $firstEn = trim((string) $sheet->getCell("E{$r}")->getValue());
            $lastEn  = trim((string) $sheet->getCell("F{$r}")->getValue());

            $email = trim((string) $sheet->getCell("G{$r}")->getValue());
            $phone = trim((string) $sheet->getCell("H{$r}")->getValue());

            $org = trim((string) $sheet->getCell("I{$r}")->getValue());
            $academic = trim((string) $sheet->getCell("J{$r}")->getValue());
            $admin = trim((string) $sheet->getCell("K{$r}")->getValue());

            $bkk = (string) $sheet->getCell("L{$r}")->getValue();
            $upc = (string) $sheet->getCell("M{$r}")->getValue();
            $province = trim((string) $sheet->getCell("N{$r}")->getValue());

            $travel = trim((string) $sheet->getCell("O{$r}")->getValue());

            $foodType = trim((string) $sheet->getCell("P{$r}")->getValue());
            $foodAllergy = trim((string) $sheet->getCell("Q{$r}")->getValue());
            $foodOther = trim((string) $sheet->getCell("R{$r}")->getValue());

            $actWorkshop = (string) $sheet->getCell("S{$r}")->getValue();
            $actConf = (string) $sheet->getCell("T{$r}")->getValue();
            $actExcur = (string) $sheet->getCell("U{$r}")->getValue();

            $presConf = (string) $sheet->getCell("V{$r}")->getValue();
            $presOral = (string) $sheet->getCell("W{$r}")->getValue();
            $presPoster = (string) $sheet->getCell("X{$r}")->getValue();

            $qr = trim((string) $sheet->getCell("AH{$r}")->getValue());

            // ถ้าแถวว่างจริง ๆ ให้ข้าม
            if ($qr === '' && $firstTh === '' && $email === '') {
                $skipped++;
                continue;
            }

            // ✅ no: ถ้าในไฟล์มี ให้ใช้ได้ แต่ถ้าซ้ำ/ว่าง ให้ต่อท้ายจาก DB
            $no = (int) $noFromFile;
            if ($no <= 0) $no = ++$lastNo;

            // ✅ province_type ตามที่คุณกำหนด: ถ้า province = กทม. -> type_1 true
            $provinceType1 = ($province === 'กรุงเทพมหานคร');
            $provinceType2 = ($province !== '' && $province !== 'กรุงเทพมหานคร');

            // ✅ boolean จาก excel (บางไฟล์จะเป็น 1/0 หรือ yes/ว่าง) เลยทำให้ยืดหยุ่น
            $toBool = fn($v) => in_array(mb_strtolower(trim((string)$v)), ['1','true','yes','y','✓','check','checked'], true);

            $payload = [
                'no' => $no,
                'register_date' => $registerDate ?: null,

                'first_name_th' => $firstTh ?: null,
                'last_name_th'  => $lastTh ?: null,
                'first_name_en' => $firstEn ?: null,
                'last_name_en'  => $lastEn ?: null,

                'email' => $email ?: null,
                'phone' => $phone ?: null,

                'organization' => $org ?: null,
                'academic_position' => $academic ?: null,
                'admin_position' => $admin ?: null,

                'province_type_1' => $provinceType1,
                'province_type_2' => $provinceType2,
                'province' => $province ?: null,

                'travel_from_province' => $travel ?: null,

                'food_type' => $foodType ?: null,
                'food_allergy' => $foodAllergy ?: null,
                'food_other_constraints' => $foodOther ?: null,

                'activity_workshop' => $toBool($actWorkshop),
                'activity_conference' => $toBool($actConf),
                'activity_excursion' => $toBool($actExcur),

                'presentation_conference' => $toBool($presConf),
                'presentation_oral' => $toBool($presOral),
                'presentation_poster' => $toBool($presPoster),

                'qr_code' => $qr ?: null,

                // ✅ append ใหม่: ให้เป็น waiting เสมอ
                'status' => 'waiting',
                'register_date1' => null,
                'register_date2' => null,
            ];

            // ✅ กันซ้ำด้วย qr_code (แนะนำสุด)
            if ($qr !== '') {
                $exists = Attendee2::where('qr_code', $qr)->first();

                if ($exists) {
                    // ถ้าต้องการ "อัปเดตข้อมูลเดิม" ให้ใช้ update
                    $exists->update($payload);
                    $updated++;
                    continue;
                }
            }

            // ถ้าไม่มี qr_code จะกันซ้ำด้วย email เป็นตัวเลือกสำรอง
            if ($qr === '' && $email !== '') {
                $exists = Attendee2::where('email', $email)->first();
                if ($exists) {
                    $exists->update($payload);
                    $updated++;
                    continue;
                }
            }

            // ✅ ถ้าไม่ซ้ำ -> create
            Attendee2::create($payload);
            $inserted++;

            // sync lastNo (กันกรณีไฟล์ให้ no ใหญ่กว่า lastNo)
            if ($no > $lastNo) $lastNo = $no;
        }

        $this->command?->info("Append done. inserted={$inserted}, updated={$updated}, skipped={$skipped}");
    }
}
