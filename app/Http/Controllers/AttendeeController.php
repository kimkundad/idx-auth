<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString(); // waiting / checked_in / all

        $registerDate = $request->string('register_date')->toString();

        $baseQuery = Attendee::query();

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

        // สถิติ (ทำจาก query แยกเพื่อความถูก)
        $total = Attendee::count();
        $checkedIn = Attendee::where('status', 'checked_in')->count();
        $waiting = Attendee::where('status', 'waiting')->count();
        $rejected = Attendee::where('status', 'rejected')->count(); // ถ้ามี
        $pending = Attendee::where('status', 'pending')->count();   // ถ้ามี

        $attendees = $baseQuery
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard', compact(
            'attendees', 'total', 'checkedIn', 'waiting', 'rejected', 'pending'
        ));
    }


    public function lookup(Request $request)
    {
        $qr = trim((string) $request->query('qr', ''));

        if ($qr === '') {
            return response()->json(['ok' => false, 'message' => 'กรุณากรอก QR Code'], 422);
        }

        $attendee = Attendee::where('qr_code', $qr)->first();

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
                'register_date' => $attendee->register_date,
                'status' => $attendee->status,
                'qr_code' => $attendee->qr_code,
                'checked_in_at' => $attendee->checked_in_at,
            ],
        ]);
    }

    public function checkin(Request $request, Attendee $attendee)
    {
        if ($attendee->status !== 'checked_in') {
            $attendee->update([
                'status' => 'checked_in',
                'checked_in_at' => now(), // ✅ now() = เวลาไทย
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'ได้ลงทะเบียนเรียบร้อยแล้ว',
                'data' => [
                    'id' => $attendee->id,
                    'status' => $attendee->status,
                    // ✅ ส่งเป็นเวลาไทยแน่นอน
                    'checked_in_at' => $attendee->checked_in_at
                        ? $attendee->checked_in_at->format('Y-m-d H:i:s')
                        : null,
                ],
            ]);
        }

        return back()->with('success', 'เช็คอินสำเร็จ');
    }

    public function edit(Attendee $attendee)
    {
        return view('attendees.edit', compact('attendee'));
    }

    public function update(Request $request, Attendee $attendee)
    {
        $data = $request->validate([
            'first_name_th' => ['nullable','string','max:255'],
            'last_name_th'  => ['nullable','string','max:255'],
            'email'         => ['nullable','string','max:255'],
            'phone'         => ['nullable','string','max:50'],
            'organization'  => ['nullable','string','max:255'],
            'status'        => ['required','string'],
        ]);

        $attendee->update($data);

        return redirect()->route('dashboard')->with('success', 'บันทึกข้อมูลแล้ว');
    }

    public function destroy(Attendee $attendee)
    {
        $attendee->delete();
        return back()->with('success', 'ลบรายการแล้ว');
    }
}
