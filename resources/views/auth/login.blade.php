<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="col-12 col-sm-10 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="mb-4 text-center">
                    <div class="fw-semibold fs-4">Admin Portal</div>
                    <div class="text-secondary small">กรุณาเข้าสู่ระบบเพื่อใช้งานหลังบ้าน</div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger small">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control form-control-lg"
                            placeholder="Admin001"
                            value="{{ old('name') }}"
                            required
                            autofocus
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control form-control-lg"
                            placeholder="••••••••"
                            required
                        >
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label small" for="remember">Remember me</label>
                        </div>

                        {{-- @if (Route::has('password.request'))
                            <a class="small text-decoration-none" href="{{ route('password.request') }}">
                                ลืมรหัสผ่าน?
                            </a>
                        @endif --}}
                    </div>

                    <button class="btn btn-primary btn-lg w-100 rounded-3">
                        เข้าสู่ระบบ
                    </button>
                </form>

                <div class="text-center text-secondary small mt-4">
                    © {{ date('Y') }} Admin System
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
