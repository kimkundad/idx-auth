<?php

namespace App\Exports;

use App\Models\Attendee2;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RegistrantsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private array $filters = [])
    {
    }

    public function collection(): Collection
    {
        $q = Attendee2::query();

        // เรียงตาม "ลำดับ" ให้เป็นตัวเลขจริง (no เป็น string)
        $q->orderByRaw('CAST(`no` AS UNSIGNED) ASC');

        // (optional) export ตามตัวกรองหน้า dashboard
        if (!empty($this->filters['q'])) {
            $kw = trim($this->filters['q']);
            $q->where(function ($w) use ($kw) {
                $w->where('first_name_th', 'like', "%{$kw}%")
                    ->orWhere('last_name_th', 'like', "%{$kw}%")
                    ->orWhere('first_name_en', 'like', "%{$kw}%")
                    ->orWhere('last_name_en', 'like', "%{$kw}%")
                    ->orWhere('email', 'like', "%{$kw}%")
                    ->orWhere('phone', 'like', "%{$kw}%")
                    ->orWhere('organization', 'like', "%{$kw}%")
                    ->orWhere('qr_code', 'like', "%{$kw}%");
            });
        }

        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            $q->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['register_date'])) {
            $q->whereDate('register_date', $this->filters['register_date']);
        }

        return $q->get();
    }

    public function headings(): array
    {
        // ชื่อหัวตารางให้ตรงกับไฟล์ XLSX ที่คุณใช้งาน (ชุดใหม่)
        return [
            'ลำดับ',
            'วันที่สมัคร',

            'ชื่อ (ไทย)',
            'นามสกุล (ไทย)',
            'ชื่อ (อังกฤษ)',
            'นามสกุล (อังกฤษ)',

            'อีเมล',
            'เบอร์โทรศัพท์',

            'สังกัด',
            'ตำแหน่งวิชาการ',
            'ตำแหน่งบริหาร',

            'กรุงเทพฯ',
            'ต่างจังหวัด',
            'เขต / จังหวัด',

            'วิธีการเดินทาง',

            'ประเภทอาหาร',
            'แพ้อาหาร',
            'ข้อจำกัดอื่น ๆ',

            'กิจกรรม: Workshop',
            'กิจกรรม: Conference',
            'กิจกรรม: Excursion',

            'การนำเสนอ: Conference',
            'การนำเสนอ: Oral',
            'การนำเสนอ: Poster',

            'QR Code',
            'เวลาเช็คอิน (ก่อน 15 ม.ค.)',
            'เวลาเช็คอิน (15 ม.ค.)',
            'สถานะ',
        ];
    }

    public function map($a): array
    {
        // เวลาเช็คอิน: ใช้ register_date1 / register_date2 (datetime)
        $checkin1 = $this->fmtBkkDateTime($a->register_date1);
        $checkin2 = $this->fmtBkkDateTime($a->register_date2);

        return [
            $a->no ?? '',
            $this->fmtBkkDate($a->register_date),

            $a->first_name_th ?? '',
            $a->last_name_th ?? '',
            $a->first_name_en ?? '',
            $a->last_name_en ?? '',

            $a->email ?? '',
            $a->phone ?? '',

            $a->organization ?? '',
            $a->academic_position ?? '',
            $a->admin_position ?? '',

            $this->boolText($a->province_type_1),
            $this->boolText($a->province_type_2),
            $a->province ?? '',

            $a->travel_from_province ?? '',

            $a->food_type ?? '',
            $a->food_allergy ?? '',
            $a->food_other_constraints ?? '',

            $this->boolText($a->activity_workshop),
            $this->boolText($a->activity_conference),
            $this->boolText($a->activity_excursion),

            $this->boolText($a->presentation_conference),
            $this->boolText($a->presentation_oral),
            $this->boolText($a->presentation_poster),

            $a->qr_code ?? '',
            $checkin1,
            $checkin2,
            $a->status ?? '',
        ];
    }

    private function fmtBkkDate($value): string
    {
        if (!$value) return '';
        try {
            return Carbon::parse($value)->timezone('Asia/Bangkok')->format('Y-m-d');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function fmtBkkDateTime($value): string
    {
        if (!$value) return '';
        try {
            return Carbon::parse($value)->timezone('Asia/Bangkok')->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function boolText($value): string
    {
        if (is_null($value)) return '';
        return $value ? 'TRUE' : 'FALSE';
    }
}
