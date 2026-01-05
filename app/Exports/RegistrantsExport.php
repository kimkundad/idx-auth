<?php

namespace App\Exports;

use App\Models\Attendee;
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
        $q = Attendee::query()->orderBy('id');

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
        // ตรงตามไฟล์ Excel ที่แนบ (sheet Registrants)
        return [
            'ชื่อ (ไทย)',
            'นามสกุล (ไทย)',
            'ชื่อ (อังกฤษ)',
            'นามสกุล (อังกฤษ)',
            'อีเมล',
            'เบอร์โทรศัพท์',
            'สังกัด',
            'ตำแหน่งทางวิชาการ',
            'ตำแหน่งบริหาร',
            'ประเภทจังหวัด',
            'เขต (กรุงเทพ)',
            'จังหวัด',
            'วิธีการเดินทางจากต่างจังหวัด',
            'วิธีการเดินทางจากที่พัก',
            'ประเภทอาหาร',
            'แพ้อาหาร',
            'กิจกรรมที่เข้าร่วม',
            'ประเภทการนำเสนอ',
            'QR Code',
            'วันที่สมัคร',
            'เวลาเช็คอิน',
            'สถานะ',
        ];
    }

    private function thaiDate(?string $date): string
    {
        if (!$date) return '';

        try {
            $dt = Carbon::parse($date)->timezone('Asia/Bangkok');
        } catch (\Throwable $e) {
            // ถ้า parse ไม่ได้ ก็ส่งค่าดิบกลับ
            return (string) $date;
        }

        $thaiMonths = [
            1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
            7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'
        ];

        $d = $dt->day;
        $m = $thaiMonths[$dt->month] ?? $dt->month;
        $y = $dt->year + 543; // พ.ศ.

        return "{$d} {$m} {$y}";
    }

    public function map($a): array
    {

        $checkedIn = '';

        if (!empty($a->checked_in_at)) {
            $checkedIn = \Carbon\Carbon::parse($a->checked_in_at)
                ->timezone('Asia/Bangkok')
                ->format('Y-m-d H:i:s');
        }


        return [
            $a->first_name_th ?? '',
            $a->last_name_th ?? '',
            $a->first_name_en ?? '',
            $a->last_name_en ?? '',
            $a->email ?? '',
            $a->phone ?? '',
            $a->organization ?? '',
            $a->academic_position ?? '',
            $a->admin_position ?? '',
            $a->province_type ?? '',
            $a->bangkok_zone ?? '',
            $a->province ?? '',
            $a->travel_from_province ?? '',
            $a->travel_from_hotel ?? '',
            $a->food_type ?? '',
            $a->food_allergy ?? '',
            $a->activity ?? '',
            $a->presentation_type ?? '',
            $a->qr_code ?? '',
            $this->thaiDate($a->register_date),
            $checkedIn,
            $a->status ?? '',
        ];
    }
}
