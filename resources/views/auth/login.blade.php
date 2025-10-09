<!doctype html>
<html class="no-js" lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="HRIS Pegasus - Sistem login karyawan">

    <title>HRIS Login</title>
    <link rel="icon" href="{{ asset('assets/favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/my-task.style.min.css') }}">
</head>

<body data-mytask="theme-indigo">

    <div id="mytask-layout">

        <div class="main p-2 py-3 p-xl-5 ">
            <div class="body d-flex p-0 p-xl-5">
                <div class="container-xxl">
                    <div class="row g-0">
                        <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center rounded-lg auth-h100">
                            <div style="max-width: 25rem;">
                                <div class="text-center mb-5">
                                    <svg width="4rem" fill="currentColor" class="bi bi-clipboard-check" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z" />
                                        <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z" />
                                        <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z" />
                                    </svg>
                                </div>
                                <div class="mb-5">
                                    <h2 class="color-900 text-center">HRIS Pegasus Website</h2>
                                </div>
                                <div>
                                    <img src="{{ asset('assets/images/login-img.svg') }}" alt="login-img">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 d-flex justify-content-center align-items-center border-0 rounded-lg auth-h100">
                            <div class="w-100 p-3 p-md-5 card border-0 bg-dark text-light" style="max-width: 32rem;">
                                <!-- Error Messages -->
                                @if($errors->any())
                                <div class="card border-danger mb-3" id="errorAlert">
                                    <div class="card-body text-danger">
                                        <div class="d-flex align-items-center">
                                            <svg width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle me-2" viewBox="0 0 16 16">
                                                <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z" />
                                                <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z" />
                                            </svg>
                                            <h6 class="card-title mb-0">Email atau kata sandi salah</h6>
                                        </div>
                                        <p class="card-text mt-2 mb-0">Silakan periksa kembali email dan kata sandi Anda.</p>
                                    </div>
                                </div>
                                @endif

                                @if(session('status'))
                                <div class="card border-success mb-3" id="successAlert">
                                    <div class="card-body text-success">
                                        <div class="d-flex align-items-center">
                                            <svg width="20" height="20" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z" />
                                            </svg>
                                            <h6 class="card-title mb-0">Berhasil</h6>
                                        </div>
                                        <p class="card-text mt-2 mb-0">{{ session('status') }}</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Rate Limiting Alert -->
                                @if(session('login_attempts'))
                                <div class="card border-warning mb-3" id="rateLimitAlert">
                                    <div class="card-body text-warning">
                                        <div class="d-flex align-items-center">
                                            <svg width="20" height="20" fill="currentColor" class="bi bi-shield-exclamation me-2" viewBox="0 0 16 16">
                                                <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z" />
                                                <path d="M7.001 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z" />
                                            </svg>
                                            <h6 class="card-title mb-0">Peringatan Keamanan</h6>
                                        </div>
                                        <p class="card-text mt-2 mb-0">
                                            @if(session('login_attempts') >= 5)
                                            Terlalu banyak percobaan login. Silakan tunggu 15 menit atau reset sandi Anda.
                                            @else
                                            Percobaan login gagal: {{ session('login_attempts') }}/5. Setelah 5 kali gagal, akun akan terkunci sementara.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                @endif
                                <!-- End Error Messages -->

                                <!-- Form -->
                                <form method="POST" action="{{ route('login') }}" class="row g-1 p-3 p-md-4" id="loginForm">
                                    @csrf
                                    <div class="col-12 text-center mb-1 mb-lg-5">
                                        <h1>Masuk</h1>
                                        <span>Masukkan Alamat Email dan Sandi Untuk Masuk</span>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-2">
                                            <label class="form-label">Alamat Email</label>
                                            <input type="email" name="email" class="form-control form-control-lg" placeholder="name@example.com" value="{{ old('email', Cookie::get('remember_email')) }}" required aria-describedby="emailHelp">
                                            <div id="emailHelp" class="form-text text-light">Masukkan alamat email yang terdaftar</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-2">
                                            <div class="form-label">
                                                <span class="d-flex justify-content-between align-items-center">
                                                    Sandi
                                                    <a class="text-secondary" href="{{ route('password.request') }}">Lupa Sandi?</a>
                                                </span>
                                            </div>
                                            <div class="input-group">
                                                <input type="password" name="password" class="form-control form-control-lg" placeholder="***************" required autocomplete="current-password" id="passwordInput">
                                                <button type="button" class="btn btn-outline-light" id="togglePassword">
                                                    <svg width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16" id="eyeIcon">
                                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
                                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="flexCheckDefault" {{ Cookie::get('remember_email') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flexCheckDefault">
                                                Ingat Saya
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center mt-4">
                                        <button type="submit" class="btn btn-lg btn-block btn-light lift text-uppercase" id="submitBtn"
                                            @if(session('login_attempts')>= 5) disabled @endif>
                                            @if(session('login_attempts') >= 5)
                                            🔒 Akun Terkunci
                                            @else
                                            {{ __('Masuk') }}
                                            @endif
                                        </button>
                                    </div>

                                    @if(session('login_attempts') >= 5)
                                    <div class="col-12 text-center mt-3">
                                        <div class="alert alert-warning">
                                            <small>
                                                <strong>Akun terkunci sementara.</strong><br>
                                                Silakan tunggu 15 menit atau
                                                <a href="{{ route('password.request') }}" class="alert-link">reset sandi Anda</a>.
                                            </small>
                                        </div>
                                    </div>
                                    @endif
                                </form>
                                <!-- End Form -->

                                <!-- Login Attempts Counter (Hidden) -->
                                <div id="loginAttempts" data-attempts="{{ session('login_attempts', 0) }}" style="display: none;"></div>
                            </div>
                        </div>
                    </div> <!-- End Row -->
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/bundles/libscripts.bundle.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            const errorAlert = document.getElementById('errorAlert');
            const successAlert = document.getElementById('successAlert');
            const rateLimitAlert = document.getElementById('rateLimitAlert');
            const loginAttempts = document.getElementById('loginAttempts');
            const attemptsCount = loginAttempts ? parseInt(loginAttempts.getAttribute('data-attempts')) : 0;

            // Auto hide alerts after 5 seconds
            const alerts = [errorAlert, successAlert, rateLimitAlert];
            alerts.forEach(alert => {
                if (alert) {
                    setTimeout(() => {
                        alert.style.opacity = '0';
                        alert.style.transition = 'opacity 0.5s ease';
                        setTimeout(() => {
                            alert.remove();
                        }, 500);
                    }, 5000);
                }
            });

            // Form submission handling
            if (loginForm && submitBtn) {
                loginForm.addEventListener('submit', function(e) {
                    // Jika sudah mencapai limit, prevent submission
                    if (attemptsCount >= 5) {
                        e.preventDefault();
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Loading...';

                    // Simpan email ke cookie jika "Ingat Saya" dicentang
                    const rememberMe = document.getElementById('flexCheckDefault').checked;
                    const email = document.querySelector('input[name="email"]').value;

                    if (rememberMe) {
                        document.cookie = `remember_email=${email}; max-age=${60*60*24*30}; path=/`; // 30 days
                    } else {
                        document.cookie = 'remember_email=; max-age=0; path=/';
                    }
                });
            }

            // Toggle password visibility
            if (togglePassword && passwordInput && eyeIcon) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    if (type === 'text') {
                        eyeIcon.innerHTML = '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>';
                    } else {
                        eyeIcon.innerHTML = '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>';
                    }
                });
            }

            // Disable form jika sudah mencapai limit
            if (attemptsCount >= 5) {
                const inputs = loginForm.querySelectorAll('input');
                inputs.forEach(input => {
                    input.disabled = true;
                });
            }
        });
    </script>

</body>

</html>