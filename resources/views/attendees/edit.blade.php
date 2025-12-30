<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>แก้ไขผู้เข้าร่วม</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
<div class="container py-4">
  <a href="{{ route('dashboard') }}" class="btn btn-link mb-3">← กลับ</a>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
      <div class="fs-4 fw-semibold mb-3">แก้ไขข้อมูลผู้เข้าร่วม</div>

      <form method="POST" action="{{ route('attendees.update', $attendee) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">ชื่อ (TH)</label>
            <input class="form-control" name="first_name_th" value="{{ old('first_name_th', $attendee->first_name_th) }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">สกุล (TH)</label>
            <input class="form-control" name="last_name_th" value="{{ old('last_name_th', $attendee->last_name_th) }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">อีเมล</label>
            <input class="form-control" name="email" value="{{ old('email', $attendee->email) }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">โทร</label>
            <input class="form-control" name="phone" value="{{ old('phone', $attendee->phone) }}">
          </div>

          <div class="col-md-8">
            <label class="form-label">องค์กร</label>
            <input class="form-control" name="organization" value="{{ old('organization', $attendee->organization) }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">สถานะ</label>
            <select class="form-select" name="status">
              <option value="waiting" {{ old('status',$attendee->status)==='waiting'?'selected':'' }}>รอเช็คอิน</option>
              <option value="checked_in" {{ old('status',$attendee->status)==='checked_in'?'selected':'' }}>เช็คอินแล้ว</option>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">ยกเลิก</a>
            <button class="btn btn-primary">บันทึก</button>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>
</body>
</html>
