<?php

namespace App\Http\Controllers;

use App\Models\Attendee2;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendeeController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString(); // waiting / checked_in / all
        $registerDate = $request->string('register_date')->toString();

        $baseQuery = Attendee2::query();

        if ($q !== '') {
            $baseQuery->where(function ($w) use ($q) {
                $w->where('first_name_th', 'like', "%{$q}%")
                  ->orWhere('last_name_th', 'like', "%{$q}%")
                  ->orWhere('first_name_en', 'like', "%{$q}%")
                  ->orWhere('last_name_en', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('qr_code', 'like', "%{$q}%")
                  ->orWhere('organization', 'like', "%{$q}%");
            });
        }

        if ($status !== '' && $status !== 'all') {
            $baseQuery->where('status', $status);
        }

        if ($registerDate !== '') {
            $baseQuery->whereDate('register_date', $registerDate);
        }

        // สถิติ
        $total     = Attendee2::count();
        $checkedIn = Attendee2::where('status', 'checked_in')
        ->whereNotNull('register_date2')
        ->count();
        $waiting   = Attendee2::where('status', 'waiting')->count();
        $rejected  = Attendee2::where('status', 'rejected')->count();
        $pending   = Attendee2::where('status', 'pending')->count();

        $attendees = $baseQuery
            ->orderByRaw('CAST(`no` AS UNSIGNED) ASC')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard', compact(
            'attendees', 'total', 'checkedIn', 'waiting', 'rejected', 'pending'
        ));
    }

    public function label(Attendee2 $attendee)
    {
        $w = 80; // mm
        $h = 70; // mm

        return view('attendees.label', compact('attendee', 'w', 'h'));
    }

    public function lookup(Request $request)
    {
        $qr = trim((string) $request->query('qr', ''));

        if ($qr === '') {
            return response()->json(['ok' => false, 'message' => 'กรุณากรอก QR Code'], 422);
        }

        $attendee = Attendee2::where('qr_code', $qr)->first();

        if (!$attendee) {
            return response()->json(['ok' => false, 'message' => 'ไม่พบข้อมูลจาก QR Code นี้'], 404);
        }

        return response()->json([
        'ok' => true,
        'data' => [
            'id' => $attendee->id,
            'full_name_th' => trim(($attendee->first_name_th ?? '').' '.($attendee->last_name_th ?? '')) ?: '-',
            'full_name_en' => trim(($attendee->first_name_en ?? '').' '.($attendee->last_name_en ?? '')) ?: '-',
            'email' => $attendee->email,
            'phone' => $attendee->phone,
            'organization' => $attendee->organization,
            'status' => $attendee->status,
            'qr_code' => $attendee->qr_code,
            'province' => $attendee->province,
            'travel_from_province' => $attendee->travel_from_province,
            'edit_url' => route('attendees.edit', $attendee),

            // ✅ เพิ่ม 2 ตัวนี้
            'activity_th' => $this->activityEn($attendee),
            'presentation_th' => $this->presentationEn($attendee),

            // ✅ เวลาเช็คอิน (ของ Attendee2 คุณใช้ register_date1/2)
            'register_date1' => optional($attendee->register_date1)->format('Y-m-d H:i:s'),
            'register_date2' => optional($attendee->register_date2)->format('Y-m-d H:i:s'),
        ],
        ]);
    }


    private function activityEn($a): string
{
    $items = [];

    if ($a->activity_workshop)   $items[] = 'Workshop';
    if ($a->activity_conference) $items[] = 'Conference';
    if ($a->activity_excursion)  $items[] = 'Excursion';

    return $items ? implode(' / ', $items) : '-';
}

private function presentationEn($a): string
{
    $items = [];

    if ($a->presentation_conference) $items[] = 'Conference';
    if ($a->presentation_oral)       $items[] = 'Oral';
    if ($a->presentation_poster)     $items[] = 'Poster';

    return $items ? implode(' / ', $items) : '-';
}


    private function activityTh($a): string
{
    $items = [];
    if ($a->activity_workshop)   $items[] = 'เวิร์กช็อป';
    if ($a->activity_conference) $items[] = 'ประชุมวิชาการ';
    if ($a->activity_excursion)  $items[] = 'ทัศนศึกษา';
    return $items ? implode(', ', $items) : '-';
}

private function presentationTh($a): string
{
    $items = [];
    if ($a->presentation_conference) $items[] = 'Conference';
    if ($a->presentation_oral)       $items[] = 'บรรยาย (Oral)';
    if ($a->presentation_poster)     $items[] = 'โปสเตอร์ (Poster)';
    return $items ? implode(', ', $items) : '-';
}

    public function checkin(Request $request, Attendee2 $attendee)
    {
        // จุดตัด: 15 ม.ค. 2026 (ทั้งวัน)
        $cutoffStart = Carbon::create(2026, 1, 15, 0, 0, 0); // 2026-01-15 00:00:00
        $cutoffEnd   = Carbon::create(2026, 1, 15, 23, 59, 59);

        $now = now();

        if ($attendee->status !== 'checked_in') {
            $payload = [
                'status' => 'checked_in',
            ];

            if ($now->lt($cutoffStart)) {
                // ก่อนวันที่ 15 → เก็บ register_date1
                $payload['register_date1'] = $now;
            } elseif ($now->betweenIncluded($cutoffStart, $cutoffEnd)) {
                // วันที่ 15 → เก็บ register_date2
                $payload['register_date2'] = $now;
            } else {
                // หลังวันที่ 15 (เผื่อใช้งานจริง) — เลือกเก็บ register_date2 ต่อเนื่อง
                $payload['register_date2'] = $now;
            }

            $attendee->update($payload);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'ได้ลงทะเบียนเรียบร้อยแล้ว',
                'data' => [
                    'id' => $attendee->id,
                    'status' => $attendee->status,
                    'register_date1' => $attendee->register_date1
                        ? $attendee->register_date1->format('Y-m-d H:i:s')
                        : null,
                    'register_date2' => $attendee->register_date2
                        ? $attendee->register_date2->format('Y-m-d H:i:s')
                        : null,
                ],
            ]);
        }

        return back()->with('success', 'เช็คอินสำเร็จ');
    }

    public function edit(Attendee2 $attendee)
    {
        return view('attendees.edit', compact('attendee'));
    }

public function update(Request $request, Attendee2 $attendee)
{
    $data = $request->validate([
        'first_name_th' => ['nullable','string','max:255'],
        'last_name_th'  => ['nullable','string','max:255'],
        'email'         => ['nullable','string','max:255'],
        'phone'         => ['nullable','string','max:50'],
        'organization'  => ['nullable','string','max:255'],

        'status'        => ['required','in:waiting,checked_in,rejected,pending'],

        'province'      => ['nullable','string','max:255'],
        'travel_from_province' => ['nullable','string','max:2000'],
    ]);

    // ---------- booleans (checkbox) ----------
    $data['activity_workshop']    = $request->boolean('activity_workshop');
    $data['activity_conference']  = $request->boolean('activity_conference');
    $data['activity_excursion']   = $request->boolean('activity_excursion');

    $data['presentation_conference'] = $request->boolean('presentation_conference');
    $data['presentation_oral']       = $request->boolean('presentation_oral');
    $data['presentation_poster']     = $request->boolean('presentation_poster');

    // ---------- province type ----------
    $province = (string) ($data['province'] ?? '');

    if ($province === 'กรุงเทพมหานคร') {
        $data['province_type_1'] = true;
        $data['province_type_2'] = false;
    } elseif ($province !== '') {
        $data['province_type_1'] = false;
        $data['province_type_2'] = true;
    } else {
        $data['province_type_1'] = false;
        $data['province_type_2'] = false;
    }

    // ✅ ถ้าเปลี่ยนสถานะเป็น waiting → ล้างวันที่ลงทะเบียน
    if ($data['status'] === 'waiting') {
        $data['register_date1'] = null;
        $data['register_date2'] = null;
    }

    $attendee->update($data);

    return redirect()
        ->route('dashboard')
        ->with('success', 'บันทึกข้อมูลแล้ว');
}



public function store(Request $request)
{
    $data = $request->validate([
        'first_name_th' => ['nullable','string','max:255'],
        'last_name_th'  => ['nullable','string','max:255'],
        'email'         => ['nullable','string','max:255'],
        'phone'         => ['nullable','string','max:50'],
        'organization'  => ['nullable','string','max:255'],

        'status'        => ['required','in:waiting,checked_in,rejected,pending'],

        'province'      => ['nullable','string','max:255'],
        'travel_from_province' => ['nullable','string','max:2000'],
    ]);

    // ✅ no ต่อเลขอัตโนมัติ
    $maxNo = DB::table('attendees2')->max(DB::raw('CAST(`no` AS UNSIGNED)'));
    $data['no'] = ($maxNo ? ((int)$maxNo + 1) : 1);

    // ✅ วันที่เพิ่มข้อมูล
    $data['register_date'] = now();

    // ✅ checkbox booleans
    $data['activity_workshop']   = $request->boolean('activity_workshop');
    $data['activity_conference'] = $request->boolean('activity_conference');
    $data['activity_excursion']  = $request->boolean('activity_excursion');

    $data['presentation_conference'] = $request->boolean('presentation_conference');
    $data['presentation_oral']       = $request->boolean('presentation_oral');
    $data['presentation_poster']     = $request->boolean('presentation_poster');

    // ✅ province_type จากจังหวัด
    $province = (string) ($data['province'] ?? '');
    if ($province === 'กรุงเทพมหานคร') {
        $data['province_type_1'] = true;
        $data['province_type_2'] = false;
    } elseif ($province !== '') {
        $data['province_type_1'] = false;
        $data['province_type_2'] = true;
    } else {
        $data['province_type_1'] = false;
        $data['province_type_2'] = false;
    }

    // ✅ ถ้า status = checked_in -> set register_date1/2 ตามวันที่ 14/15
    if (($data['status'] ?? '') === 'checked_in') {
        $now = now()->timezone('Asia/Bangkok');
        $d14 = Carbon::create(2026, 1, 14, 0, 0, 0, 'Asia/Bangkok')->toDateString();
        $d15 = Carbon::create(2026, 1, 15, 0, 0, 0, 'Asia/Bangkok')->toDateString();

        if ($now->toDateString() <= $d14) {
            $data['register_date1'] = $now;
            $data['register_date2'] = null;
        } elseif ($now->toDateString() === $d15) {
            $data['register_date2'] = $now;
            $data['register_date1'] = null;
        } else {
            // ถ้าหลัง 15 (กันเหนียว) ให้ไปลงวันที่ 15
            $data['register_date2'] = $now;
            $data['register_date1'] = null;
        }
    } else {
        // ถ้าไม่ได้ checked_in จะปล่อยว่างไว้ (หรือจะเคลียร์ก็ได้)
        $data['register_date1'] = null;
        $data['register_date2'] = null;
    }

    Attendee2::create($data);

    return redirect()->route('dashboard')
        ->with('success', 'เพิ่มข้อมูลผู้เข้าร่วมเรียบร้อยแล้ว');
}


public function lookupById(Attendee2 $attendee)
{
    return response()->json([
        'ok' => true,
        'data' => [
            'id' => $attendee->id,
            'full_name_th' => trim($attendee->first_name_th.' '.$attendee->last_name_th),
            'email' => $attendee->email,
            'phone' => $attendee->phone,
            'organization' => $attendee->organization,
            'province' => $attendee->province,
            'travel_from_province' => $attendee->travel_from_province,
            'activity_th' => $this->activityEn($attendee),
            'presentation_th' => $this->presentationEn($attendee),
            'status' => $attendee->status,
            'edit_url' => route('attendees.edit', $attendee),
            'register_date1' => $attendee->register_date1,
            'register_date2' => $attendee->register_date2,
        ],
    ]);
}





    public function destroy(Attendee2 $attendee)
    {
        $attendee->delete();
        return back()->with('success', 'ลบรายการแล้ว');
    }


    public function create()
    {
        return view('attendees.create');
    }
}
