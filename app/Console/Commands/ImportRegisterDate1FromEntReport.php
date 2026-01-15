<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendee2;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportRegisterDate1FromEntReport extends Command
{
    protected $signature = 'ent:import-register-date1 {file}';
    protected $description = 'Import column A (no) and column Z (checkin datetime) into attendees2.register_date1 for dates before 2026-01-15';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!is_file($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $cutoff = Carbon::create(2026, 1, 15, 0, 0, 0, 'Asia/Bangkok');

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $updated = 0;
        $skipped = 0;
        $notFound = 0;

        // สมมติ row 1 เป็นหัวตาราง => เริ่มที่ 2
        for ($row = 2; $row <= $highestRow; $row++) {
            $noRaw = $sheet->getCell("A{$row}")->getValue();
            $dtRaw = $sheet->getCell("Z{$row}")->getValue();

            $no = trim((string)$noRaw);
            if ($no === '' || $dtRaw === null || $dtRaw === '') {
                $skipped++;
                continue;
            }

            // แปลง datetime จาก Excel (อาจเป็น string หรือ excel serial)
            try {
                if (is_numeric($dtRaw)) {
                    // Excel serial date
                    $dt = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dtRaw))
                        ->timezone('Asia/Bangkok');
                } else {
                    $dt = Carbon::parse($dtRaw, 'Asia/Bangkok');
                }
            } catch (\Throwable $e) {
                $this->warn("Row {$row}: invalid datetime in Z = {$dtRaw}");
                $skipped++;
                continue;
            }

            // เงื่อนไข "ก่อน 15 ม.ค. 2026"
            if ($dt->greaterThanOrEqualTo($cutoff)) {
                $skipped++;
                continue;
            }

            // อัปเดต attendees2: ค้นด้วย no
            // ถ้า no ยังเป็น varchar อยู่ แนะนำใช้ CAST กันปัญหา '5' vs '005'
            $query = Attendee2::query()
                ->whereRaw('CAST(`no` AS UNSIGNED) = ?', [(int)$no]);

            $attendee = $query->first();

            if (!$attendee) {
                $notFound++;
                continue;
            }

            $attendee->register_date1 = $dt->format('Y-m-d H:i:s');
            $attendee->save();

            $updated++;
        }

        $this->info("Done. Updated: {$updated}, Not found: {$notFound}, Skipped: {$skipped}");
        return self::SUCCESS;
    }
}
