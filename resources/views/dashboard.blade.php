<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dashboard.js'])
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">Admin Portal</a>

            <div class="ms-auto dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    {{ auth()->user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item">ออกจากระบบ</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <div class="fs-4 fw-semibold">ภาพรวมผู้เข้าร่วมงาน</div>
                <div class="text-secondary small">ค้นหา / กรอง / เช็คอิน / แก้ไขข้อมูล</div>
            </div>

            <div class="d-flex gap-2">
             <a class="btn btn-primary" href="{{ route('attendees.create') }}">
                     เพิ่มข้อมูล
                </a>
                <a class="btn btn-dark"
                    href="{{ route('attendees.export', request()->only(['q', 'status', 'register_date'])) }}">
                    Export ข้อมูล
                </a>
                {{-- <button class="btn btn-outline-dark" disabled>Logs</button> --}}
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Stat cards --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="text-secondary small">จำนวนผู้เข้าร่วม</div>
                        <div class="display-6 fw-semibold">{{ number_format($total) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="text-secondary small">เช็คอินแล้ว</div>
                        <div class="display-6 fw-semibold">{{ number_format($checkedIn) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="text-secondary small">รอเช็คอิน</div>
                        <div class="display-6 fw-semibold">{{ number_format($waiting) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="text-secondary small">อื่น ๆ (ถ้ามี)</div>
                        <div class="display-6 fw-semibold">{{ number_format($pending + $rejected) }}</div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold">สแกน / ค้นหา QR Code</label>
                        <input id="qrInput" type="text" class="form-control form-control-lg"
                            placeholder="สแกน QR แล้วกด Enter" autocomplete="off">
                        <div class="form-text">รองรับเครื่องสแกนที่ยิงค่าเป็นข้อความ แล้วส่ง Enter</div>
                    </div>

                    <div class="col-12 col-lg-6 d-flex justify-content-lg-end gap-2">
                        <button id="qrClearBtn" type="button" class="btn btn-outline-secondary">
                            ล้างช่อง
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="GET" action="{{ route('dashboard') }}">
                    <div class="col-12 col-lg-5">
                        <label class="form-label">ค้นหา</label>
                        <input type="text" name="q" class="form-control"
                            placeholder="ค้นหา: ชื่อ / อีเมล / โทร / QR / องค์กร" value="{{ request('q') }}">
                    </div>

                    <div class="col-12 col-lg-3">
                        <label class="form-label">สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>ทั้งหมด
                            </option>
                            <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>รอเช็คอิน
                            </option>
                            <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>
                                เช็คอินแล้ว</option>
                        </select>
                    </div>

                    <div class="col-12 col-lg-2">
                        <label class="form-label">วันที่สมัคร</label>
                        <input type="date" name="register_date" class="form-control"
                            value="{{ request('register_date') }}">
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <button class="btn btn-dark">กรอง</button>
                        <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">ล้างตัวกรอง</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        {{-- Table --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle text-nowrap">
                        <thead class="table-light table-head-sm">
                            <tr>
                                <th>ลำดับ</th>
                                <th>วันที่สมัคร</th>

                                <th>ชื่อ (ไทย)</th>
                                <th>นามสกุล (ไทย)</th>
                                <th>ชื่อ (อังกฤษ)</th>
                                <th>นามสกุล (อังกฤษ)</th>

                                <th>อีเมล</th>
                                <th>โทรศัพท์</th>

                                <th>สังกัด</th>
                                <th>ตำแหน่งวิชาการ</th>
                                <th>ตำแหน่งบริหาร</th>

                                <th>กรุงเทพฯ</th>
                                <th>ต่างจังหวัด</th>
                                <th>เขต / จังหวัด</th>

                                <th>วิธีการเดินทาง</th>

                                <th>ประเภทอาหาร</th>
                                <th>แพ้อาหาร</th>
                                <th>ข้อจำกัดอื่น ๆ</th>

                                <th>กิจกรรม: Workshop</th>
                                <th>กิจกรรม: Conference</th>
                                <th>กิจกรรม: Excursion</th>

                                <th>การนำเสนอ: Conference</th>
                                <th>การนำเสนอ: Oral</th>
                                <th>การนำเสนอ: Poster</th>

                                <th>QR Code</th>
                                <th>วันที่เช็คอิน (ก่อน 15 ม.ค.)</th>
                                <th>วันที่เช็คอิน (15 ม.ค.)</th>
                                <th>สถานะ</th>

                                <th class="text-end">จัดการ</th>
                            </tr>
                        </thead>

                        <tbody class="table-body-sm">
                            @forelse($attendees as $idx => $a)
                                <tr>

                                    <td>{{ $a->no ?? '-' }}</td>
                                    <td>{{ $a->register_date ? $a->register_date->format('Y-m-d') : '-' }}</td>

                                    <td>{{ $a->first_name_th ?? '-' }}</td>
                                    <td>{{ $a->last_name_th ?? '-' }}</td>
                                    <td>{{ $a->first_name_en ?? '-' }}</td>
                                    <td>{{ $a->last_name_en ?? '-' }}</td>

                                    <td>{{ $a->email ?? '-' }}</td>
                                    <td>{{ $a->phone ?? '-' }}</td>

                                    <td>{{ $a->organization ?? '-' }}</td>
                                    <td>{{ $a->academic_position ?? '-' }}</td>
                                    <td>{{ $a->admin_position ?? '-' }}</td>

                                    <td>{{ is_null($a->province_type_1) ? '-' : ($a->province_type_1 ? 'TRUE' : 'FALSE') }}
                                    </td>
                                    <td>{{ is_null($a->province_type_2) ? '-' : ($a->province_type_2 ? 'TRUE' : 'FALSE') }}
                                    </td>
                                    <td>{{ $a->province ?? '-' }}</td>

                                    <td>{{ $a->travel_from_province ?? '-' }}</td>

                                    <td>{{ $a->food_type ?? '-' }}</td>
                                    <td>{{ $a->food_allergy ?? '-' }}</td>
                                    <td>{{ $a->food_other_constraints ?? '-' }}</td>

                                    <td>{{ is_null($a->activity_workshop) ? '-' : ($a->activity_workshop ? 'TRUE' : 'FALSE') }}
                                    </td>
                                    <td>{{ is_null($a->activity_conference) ? '-' : ($a->activity_conference ? 'TRUE' : 'FALSE') }}
                                    </td>
                                    <td>{{ is_null($a->activity_excursion) ? '-' : ($a->activity_excursion ? 'TRUE' : 'FALSE') }}
                                    </td>

                                    <td>{{ is_null($a->presentation_conference) ? '-' : ($a->presentation_conference ? 'TRUE' : 'FALSE') }}
                                    </td>
                                    <td>{{ is_null($a->presentation_oral) ? '-' : ($a->presentation_oral ? 'TRUE' : 'FALSE') }}
                                    </td>
                                    <td>{{ is_null($a->presentation_poster) ? '-' : ($a->presentation_poster ? 'TRUE' : 'FALSE') }}
                                    </td>

                                    <td>{{ $a->qr_code ?? '-' }}</td>
                                    <td>{{ $a->register_date1 ? $a->register_date1->format('Y-m-d H:i:s') : '-' }}</td>
                                    <td>{{ $a->register_date2 ? $a->register_date2->format('Y-m-d H:i:s') : '-' }}</td>

                                    <td>
                                        @if ($a->status === 'checked_in')
                                            <span class="badge text-bg-success">checked_in</span>
                                        @elseif($a->status === 'waiting')
                                            <span class="badge text-bg-warning">waiting</span>
                                        @elseif($a->status === 'rejected')
                                            <span class="badge text-bg-danger">rejected</span>
                                        @else
                                            <span class="badge text-bg-secondary">{{ $a->status ?? '-' }}</span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            {{-- Check-in --}}
                                            <form method="POST" action="{{ route('attendees.checkin', $a) }}">
                                                @csrf
                                                <button class="btn btn-success btn-sm"
                                                    {{ $a->status === 'checked_in' ? 'disabled' : '' }}>
                                                    เช็คอิน
                                                </button>
                                            </form>

                                            {{-- Edit --}}
                                            <a class="btn btn-outline-primary btn-sm"
                                                href="{{ route('attendees.edit', $a) }}">
                                                แก้ไข
                                            </a>

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('attendees.destroy', $a) }}"
                                                onsubmit="return confirm('ยืนยันลบรายการนี้?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm">ลบ</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="30" class="text-center text-secondary py-5">ไม่พบข้อมูล</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    {{ $attendees->links() }}
                </div>
            </div>
        </div>


    </div>


    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4 overflow-hidden">

                <div class="modal-header bg-white">
                    <div>
                        <div class="fs-5 fw-semibold">ข้อมูลผู้เข้าร่วมงาน</div>
                        <div class="text-secondary small" id="qrModalSub">ตรวจสอบข้อมูลก่อนเช็คอิน</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div id="qrModalAlert" class="alert d-none mb-3" role="alert"></div>

                    <div id="emptyState" class="d-none text-center py-4">
                        <div class="display-6">🔎</div>
                        <div class="fw-semibold mt-2">ไม่พบข้อมูลจาก QR Code นี้</div>
                        <div class="text-secondary small">ตรวจสอบ QR Code หรือพิมพ์ใหม่อีกครั้ง</div>
                    </div>

                    <div class="row g-3" id="attendeeBlock">
                        <div class="col-md-8">
                            <div class="p-3 bg-light rounded-4">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <div class="text-secondary small">ชื่อ-สกุล (TH)</div>
                                        <div class="fs-4 fw-semibold" id="mNameTh">-</div>

                                    </div>
                                    <span class="badge rounded-pill text-bg-warning align-self-start"
                                        id="mStatusBadge">รอเช็คอิน</span>
                                </div>

                                <hr class="my-3">

                                <div class="row g-2 small">
                                    <div class="col-12 col-md-6">
                                        <div class="text-secondary">อีเมล</div>
                                        <div class="fw-semibold" id="mEmail">-</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="text-secondary">โทร</div>
                                        <div class="fw-semibold" id="mPhone">-</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-secondary">องค์กร</div>
                                        <div class="fw-semibold" id="mOrg">-</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="text-secondary">กิจกรรมที่เข้าร่วม</div>
                                        <div class="fw-semibold" id="mActivity">-</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="text-secondary">ประเภทการนำเสนอ</div>
                                        <div class="fw-semibold" id="mPresentation">-</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                    <div class="text-secondary">ประเภท</div>
                                    <div class="fw-semibold" id="mProvince">-</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                    <div class="text-secondary">วิธีการเดินทาง</div>
                                    <div class="fw-semibold" id="mTravel">-</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                    <div class="text-secondary">วันที่ลงทะเบียนวันที่ 14</div>
                                    <div class="fw-semibold" id="mRegisterDate1">-</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                    <div class="text-secondary">วันที่ลงทะเบียนวันที่ 15</div>
                                    <div class="fw-semibold" id="mRegisterDate2">-</div>
                                    </div>


                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 border rounded-4 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="fw-semibold mb-2">การดำเนินการ</div>
                                    <div class="text-secondary small mb-3">
                                        หากข้อมูลถูกต้อง กดเช็คอินเพื่อบันทึกการเข้าร่วมงาน
                                    </div>

                                    <div id="successBlock" class="d-none text-center p-3">
                                        <div class="display-4">✅</div>
                                        <div class="fw-semibold fs-5 mt-2">ได้ลงทะเบียนเรียบร้อยแล้ว</div>
                                        <div class="text-secondary small mt-1" id="successTime"></div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button id="checkinBtn" type="button" class="btn btn-success btn-lg">
                                        เช็คอิน
                                    </button>
                                    <button id="printBtn" type="button" class="btn btn-outline-dark btn-lg">
                                        🖨️ พิมพ์
                                    </button>
                                    <a id="editBtn" href="#" class="btn btn-outline-primary btn-lg">
                                    ✏️ แก้ไขข้อมูล
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        ปิด
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="mAttendeeId" value="">
                </div>
            </div>
        </div>
    </div>




</body>

</html>
