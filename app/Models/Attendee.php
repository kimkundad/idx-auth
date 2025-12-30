<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Attendee extends Model
{
    //
    protected $guarded = [];

    protected function registerDate(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if ($value === null || trim((string)$value) === '') return null;

                // Excel serial number
                if (is_numeric($value)) {
                    $unix = ((int)$value - 25569) * 86400;
                    return gmdate('Y-m-d', $unix);
                }

                $text = trim((string)$value);

                // dd/mm/yyyy หรือ dd-mm-yyyy
                if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $text, $m)) {
                    $d = (int)$m[1]; $mo = (int)$m[2]; $y = (int)$m[3];
                    if ($y > 2400) $y -= 543;
                    return sprintf('%04d-%02d-%02d', $y, $mo, $d);
                }

                // ไทย: "17 ธันวาคม 2568"
                $thaiMonths = [
                    'มกราคม'=>1,'กุมภาพันธ์'=>2,'มีนาคม'=>3,'เมษายน'=>4,'พฤษภาคม'=>5,'มิถุนายน'=>6,
                    'กรกฎาคม'=>7,'สิงหาคม'=>8,'กันยายน'=>9,'ตุลาคม'=>10,'พฤศจิกายน'=>11,'ธันวาคม'=>12,
                ];

                if (preg_match('/^(\d{1,2})\s+([ก-๙]+)\s+(\d{4})$/u', $text, $m)) {
                    $d = (int)$m[1];
                    $monthName = $m[2];
                    $y = (int)$m[3];
                    if (!isset($thaiMonths[$monthName])) return null;
                    $mo = $thaiMonths[$monthName];
                    if ($y > 2400) $y -= 543;
                    return sprintf('%04d-%02d-%02d', $y, $mo, $d);
                }

                return null;
            }
        );
    }
}
