<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เพิ่มผู้เข้าร่วม</title>
  @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/attendees/edit.js'])
</head>
<body class="bg-light">
<div class="container py-4">
  <a href="{{ route('dashboard') }}" class="btn btn-link mb-3">← กลับ</a>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
      <div class="fs-4 fw-semibold mb-3">เพิ่มข้อมูลผู้เข้าร่วม</div>

      {{-- แสดง error (ถ้ามี) --}}
      @if($errors->any())
        <div class="alert alert-danger">
          <div class="fw-semibold mb-1">บันทึกไม่สำเร็จ</div>
          <ul class="mb-0">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('attendees.store') }}">
        @csrf

        <div class="row g-3">

          {{-- Basic --}}
          <div class="col-md-6">
            <label class="form-label">ชื่อ (TH)</label>
            <input class="form-control" name="first_name_th" value="{{ old('first_name_th') }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">สกุล (TH)</label>
            <input class="form-control" name="last_name_th" value="{{ old('last_name_th') }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">อีเมล</label>
            <input class="form-control" name="email" value="{{ old('email') }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">โทร</label>
            <input class="form-control" name="phone" value="{{ old('phone') }}">
          </div>

          <div class="col-md-8">
            <label class="form-label">องค์กร</label>
            <input class="form-control" name="organization" value="{{ old('organization') }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">สถานะ</label>
            <select class="form-select" name="status">
              @php $st = old('status', 'waiting'); @endphp
              <option value="waiting" {{ $st==='waiting'?'selected':'' }}>รอเช็คอิน</option>
              <option value="checked_in" {{ $st==='checked_in'?'selected':'' }}>เช็คอินแล้ว</option>
              <option value="rejected" {{ $st==='rejected'?'selected':'' }}>rejected</option>
              <option value="pending" {{ $st==='pending'?'selected':'' }}>pending</option>
            </select>
          </div>

          <hr class="my-2">

          {{-- Activity --}}
          <div class="col-12">
            <div class="fw-semibold mb-2">Activity (กิจกรรมที่เข้าร่วม)</div>
            <div class="d-flex flex-wrap gap-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="activity_workshop" value="1"
                  {{ old('activity_workshop') ? 'checked' : '' }}>
                <label class="form-check-label">Workshop</label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="activity_conference" value="1"
                  {{ old('activity_conference') ? 'checked' : '' }}>
                <label class="form-check-label">Conference</label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="activity_excursion" value="1"
                  {{ old('activity_excursion') ? 'checked' : '' }}>
                <label class="form-check-label">Excursion</label>
              </div>
            </div>
          </div>

          {{-- Presentation --}}
          <div class="col-12">
            <div class="fw-semibold mb-2">Present (ประเภทการนำเสนอ)</div>
            <div class="d-flex flex-wrap gap-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="presentation_conference" value="1"
                  {{ old('presentation_conference') ? 'checked' : '' }}>
                <label class="form-check-label">Conference</label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="presentation_oral" value="1"
                  {{ old('presentation_oral') ? 'checked' : '' }}>
                <label class="form-check-label">Oral</label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="presentation_poster" value="1"
                  {{ old('presentation_poster') ? 'checked' : '' }}>
                <label class="form-check-label">Poster</label>
              </div>
            </div>
          </div>

          <hr class="my-2">

          {{-- Location --}}
          <div class="col-md-6">
            <label class="form-label">จังหวัด</label>

            @php $pv = old('province'); @endphp
            <select class="form-select" id="provinceSelect" name="province">
              <option value="">- เลือกจังหวัด -</option>

              {{-- (คง list จังหวัดตามเดิมของคุณ) --}}
              <option value="กรุงเทพมหานคร" {{ $pv==='กรุงเทพมหานคร'?'selected':'' }}>กรุงเทพมหานคร</option>
              <option value="กระบี่" {{ $pv==='กระบี่'?'selected':'' }}>กระบี่</option>
              <option value="กาญจนบุรี" {{ $pv==='กาญจนบุรี'?'selected':'' }}>กาญจนบุรี</option>
              <option value="กาฬสินธุ์" {{ $pv==='กาฬสินธุ์'?'selected':'' }}>กาฬสินธุ์</option>
              <option value="กำแพงเพชร" {{ $pv==='กำแพงเพชร'?'selected':'' }}>กำแพงเพชร</option>
              <option value="ขอนแก่น" {{ $pv==='ขอนแก่น'?'selected':'' }}>ขอนแก่น</option>
              <option value="จันทบุรี" {{ $pv==='จันทบุรี'?'selected':'' }}>จันทบุรี</option>
              <option value="ฉะเชิงเทรา" {{ $pv==='ฉะเชิงเทรา'?'selected':'' }}>ฉะเชิงเทรา</option>
              <option value="ชลบุรี" {{ $pv==='ชลบุรี'?'selected':'' }}>ชลบุรี</option>
              <option value="ชัยนาท" {{ $pv==='ชัยนาท'?'selected':'' }}>ชัยนาท</option>
              <option value="ชัยภูมิ" {{ $pv==='ชัยภูมิ'?'selected':'' }}>ชัยภูมิ</option>
              <option value="ชุมพร" {{ $pv==='ชุมพร'?'selected':'' }}>ชุมพร</option>
              <option value="เชียงราย" {{ $pv==='เชียงราย'?'selected':'' }}>เชียงราย</option>
              <option value="เชียงใหม่" {{ $pv==='เชียงใหม่'?'selected':'' }}>เชียงใหม่</option>
              <option value="ตรัง" {{ $pv==='ตรัง'?'selected':'' }}>ตรัง</option>
              <option value="ตราด" {{ $pv==='ตราด'?'selected':'' }}>ตราด</option>
              <option value="ตาก" {{ $pv==='ตาก'?'selected':'' }}>ตาก</option>
              <option value="นครนายก" {{ $pv==='นครนายก'?'selected':'' }}>นครนายก</option>
              <option value="นครปฐม" {{ $pv==='นครปฐม'?'selected':'' }}>นครปฐม</option>
              <option value="นครพนม" {{ $pv==='นครพนม'?'selected':'' }}>นครพนม</option>
              <option value="นครราชสีมา" {{ $pv==='นครราชสีมา'?'selected':'' }}>นครราชสีมา</option>
              <option value="นครศรีธรรมราช" {{ $pv==='นครศรีธรรมราช'?'selected':'' }}>นครศรีธรรมราช</option>
              <option value="นครสวรรค์" {{ $pv==='นครสวรรค์'?'selected':'' }}>นครสวรรค์</option>
              <option value="นนทบุรี" {{ $pv==='นนทบุรี'?'selected':'' }}>นนทบุรี</option>
              <option value="นราธิวาส" {{ $pv==='นราธิวาส'?'selected':'' }}>นราธิวาส</option>
              <option value="น่าน" {{ $pv==='น่าน'?'selected':'' }}>น่าน</option>
              <option value="บึงกาฬ" {{ $pv==='บึงกาฬ'?'selected':'' }}>บึงกาฬ</option>
              <option value="บุรีรัมย์" {{ $pv==='บุรีรัมย์'?'selected':'' }}>บุรีรัมย์</option>
              <option value="ปทุมธานี" {{ $pv==='ปทุมธานี'?'selected':'' }}>ปทุมธานี</option>
              <option value="ประจวบคีรีขันธ์" {{ $pv==='ประจวบคีรีขันธ์'?'selected':'' }}>ประจวบคีรีขันธ์</option>
              <option value="ปราจีนบุรี" {{ $pv==='ปราจีนบุรี'?'selected':'' }}>ปราจีนบุรี</option>
              <option value="ปัตตานี" {{ $pv==='ปัตตานี'?'selected':'' }}>ปัตตานี</option>
              <option value="พระนครศรีอยุธยา" {{ $pv==='พระนครศรีอยุธยา'?'selected':'' }}>พระนครศรีอยุธยา</option>
              <option value="พังงา" {{ $pv==='พังงา'?'selected':'' }}>พังงา</option>
              <option value="พัทลุง" {{ $pv==='พัทลุง'?'selected':'' }}>พัทลุง</option>
              <option value="พิจิตร" {{ $pv==='พิจิตร'?'selected':'' }}>พิจิตร</option>
              <option value="พิษณุโลก" {{ $pv==='พิษณุโลก'?'selected':'' }}>พิษณุโลก</option>
              <option value="เพชรบุรี" {{ $pv==='เพชรบุรี'?'selected':'' }}>เพชรบุรี</option>
              <option value="เพชรบูรณ์" {{ $pv==='เพชรบูรณ์'?'selected':'' }}>เพชรบูรณ์</option>
              <option value="แพร่" {{ $pv==='แพร่'?'selected':'' }}>แพร่</option>
              <option value="พะเยา" {{ $pv==='พะเยา'?'selected':'' }}>พะเยา</option>
              <option value="ภูเก็ต" {{ $pv==='ภูเก็ต'?'selected':'' }}>ภูเก็ต</option>
              <option value="มหาสารคาม" {{ $pv==='มหาสารคาม'?'selected':'' }}>มหาสารคาม</option>
              <option value="มุกดาหาร" {{ $pv==='มุกดาหาร'?'selected':'' }}>มุกดาหาร</option>
              <option value="แม่ฮ่องสอน" {{ $pv==='แม่ฮ่องสอน'?'selected':'' }}>แม่ฮ่องสอน</option>
              <option value="ยโสธร" {{ $pv==='ยโสธร'?'selected':'' }}>ยโสธร</option>
              <option value="ยะลา" {{ $pv==='ยะลา'?'selected':'' }}>ยะลา</option>
              <option value="ร้อยเอ็ด" {{ $pv==='ร้อยเอ็ด'?'selected':'' }}>ร้อยเอ็ด</option>
              <option value="ระนอง" {{ $pv==='ระนอง'?'selected':'' }}>ระนอง</option>
              <option value="ระยอง" {{ $pv==='ระยอง'?'selected':'' }}>ระยอง</option>
              <option value="ราชบุรี" {{ $pv==='ราชบุรี'?'selected':'' }}>ราชบุรี</option>
              <option value="ลพบุรี" {{ $pv==='ลพบุรี'?'selected':'' }}>ลพบุรี</option>
              <option value="ลำปาง" {{ $pv==='ลำปาง'?'selected':'' }}>ลำปาง</option>
              <option value="ลำพูน" {{ $pv==='ลำพูน'?'selected':'' }}>ลำพูน</option>
              <option value="เลย" {{ $pv==='เลย'?'selected':'' }}>เลย</option>
              <option value="ศรีสะเกษ" {{ $pv==='ศรีสะเกษ'?'selected':'' }}>ศรีสะเกษ</option>
              <option value="สกลนคร" {{ $pv==='สกลนคร'?'selected':'' }}>สกลนคร</option>
              <option value="สงขลา" {{ $pv==='สงขลา'?'selected':'' }}>สงขลา</option>
              <option value="สตูล" {{ $pv==='สตูล'?'selected':'' }}>สตูล</option>
              <option value="สมุทรปราการ" {{ $pv==='สมุทรปราการ'?'selected':'' }}>สมุทรปราการ</option>
              <option value="สมุทรสงคราม" {{ $pv==='สมุทรสงคราม'?'selected':'' }}>สมุทรสงคราม</option>
              <option value="สมุทรสาคร" {{ $pv==='สมุทรสาคร'?'selected':'' }}>สมุทรสาคร</option>
              <option value="สระแก้ว" {{ $pv==='สระแก้ว'?'selected':'' }}>สระแก้ว</option>
              <option value="สระบุรี" {{ $pv==='สระบุรี'?'selected':'' }}>สระบุรี</option>
              <option value="สิงห์บุรี" {{ $pv==='สิงห์บุรี'?'selected':'' }}>สิงห์บุรี</option>
              <option value="สุโขทัย" {{ $pv==='สุโขทัย'?'selected':'' }}>สุโขทัย</option>
              <option value="สุพรรณบุรี" {{ $pv==='สุพรรณบุรี'?'selected':'' }}>สุพรรณบุรี</option>
              <option value="สุราษฎร์ธานี" {{ $pv==='สุราษฎร์ธานี'?'selected':'' }}>สุราษฎร์ธานี</option>
              <option value="สุรินทร์" {{ $pv==='สุรินทร์'?'selected':'' }}>สุรินทร์</option>
              <option value="หนองคาย" {{ $pv==='หนองคาย'?'selected':'' }}>หนองคาย</option>
              <option value="หนองบัวลำภู" {{ $pv==='หนองบัวลำภู'?'selected':'' }}>หนองบัวลำภู</option>
              <option value="อ่างทอง" {{ $pv==='อ่างทอง'?'selected':'' }}>อ่างทอง</option>
              <option value="อุดรธานี" {{ $pv==='อุดรธานี'?'selected':'' }}>อุดรธานี</option>
              <option value="อุทัยธานี" {{ $pv==='อุทัยธานี'?'selected':'' }}>อุทัยธานี</option>
              <option value="อุตรดิตถ์" {{ $pv==='อุตรดิตถ์'?'selected':'' }}>อุตรดิตถ์</option>
              <option value="อุบลราชธานี" {{ $pv==='อุบลราชธานี'?'selected':'' }}>อุบลราชธานี</option>
              <option value="อำนาจเจริญ" {{ $pv==='อำนาจเจริญ'?'selected':'' }}>อำนาจเจริญ</option>
            </select>

            <div class="form-text">ถ้าเลือก “กรุงเทพมหานคร” ระบบจะตั้งค่าเป็นกรุงเทพฯ อัตโนมัติ</div>

            {{-- hidden สำหรับบันทึก boolean (JS จะ sync ให้) --}}
            <input type="hidden" id="province_type_1" name="province_type_1" value="{{ old('province_type_1', 0) }}">
            <input type="hidden" id="province_type_2" name="province_type_2" value="{{ old('province_type_2', 0) }}">
          </div>

          {{-- Travel --}}
          @php
            $travelRaw = old('travel_from_province', '');
            $travelLines = preg_split("/\r\n|\n|\r/", (string)$travelRaw) ?: [];
            $travelLines = array_values(array_filter(array_map('trim', $travelLines)));

            $travelHas = fn($v) => in_array($v, $travelLines, true);
            $travelOtherLine = collect($travelLines)->first(fn($x) => str_starts_with($x, 'อื่นๆ:'));
            $travelOtherText = $travelOtherLine ? trim(str_replace('อื่นๆ:', '', $travelOtherLine)) : '';
          @endphp

          <div class="col-12">
            <div class="fw-semibold mb-2">วิธีการเดินทาง (เลือกได้หลายข้อ)</div>

            <div class="row g-2">
              <div class="col-12 col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="travel_methods[]" value="รถยนต์ส่วนบุคคล"
                    {{ $travelHas('รถยนต์ส่วนบุคคล') ? 'checked' : '' }}>
                  <label class="form-check-label">รถยนต์ส่วนบุคคล</label>
                </div>

                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="travel_methods[]" value="เครื่องบิน"
                    {{ $travelHas('เครื่องบิน') ? 'checked' : '' }}>
                  <label class="form-check-label">เครื่องบิน</label>
                </div>

                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="travel_methods[]" value="รถโดยสารประจำทาง/ไม่ประจำทาง"
                    {{ $travelHas('รถโดยสารประจำทาง/ไม่ประจำทาง') ? 'checked' : '' }}>
                  <label class="form-check-label">รถโดยสารประจำทาง/ไม่ประจำทาง</label>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="travel_methods[]" value="รถไฟฟ้า"
                    {{ $travelHas('รถไฟฟ้า') ? 'checked' : '' }}>
                  <label class="form-check-label">รถไฟฟ้า</label>
                </div>

                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="travel_methods[]" value="รถแท็กซี่"
                    {{ $travelHas('รถแท็กซี่') ? 'checked' : '' }}>
                  <label class="form-check-label">รถแท็กซี่</label>
                </div>

                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="travel_methods[]" value="รถตู้ประจำทาง"
                    {{ $travelHas('รถตู้ประจำทาง') ? 'checked' : '' }}>
                  <label class="form-check-label">รถตู้ประจำทาง</label>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="travel_methods[]" value="รถจักรยานยนต์"
                    {{ $travelHas('รถจักรยานยนต์') ? 'checked' : '' }}>
                  <label class="form-check-label">รถจักรยานยนต์</label>
                </div>

                <div class="form-check">
                  <input class="form-check-input" id="travel_other_cb" type="checkbox"
                    {{ $travelOtherLine ? 'checked' : '' }}>
                  <label class="form-check-label">อื่นๆ</label>
                </div>

                <div id="travelOtherWrap" style="{{ $travelOtherLine ? '' : 'display:none;' }}" class="mt-2">
                  <input class="form-control" id="travelOtherInput" value="{{ $travelOtherText }}" placeholder="ระบุ เช่น รถตู้มหาวิทยาลัย">
                  <div class="form-text">ระบบจะบันทึกเป็น: <code>อื่นๆ: ...</code></div>
                </div>
              </div>
            </div>

            {{-- field จริงที่บันทึกลง DB --}}
            <input type="hidden" id="travel_from_province" name="travel_from_province" value="{{ $travelRaw }}">
          </div>

          <hr class="my-2">

          {{-- Save --}}
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
