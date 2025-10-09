<!doctype html>
<html class="no-js" lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="HRIS Pegasus - Reset password">

    <title>Lupa Sandi - HRIS Pegasus</title>
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
                                <!-- Session Status -->
                                @if (session('status'))
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

                                <!-- Error Messages -->
                                @if($errors->any())
                                <div class="card border-danger mb-3" id="errorAlert">
                                    <div class="card-body text-danger">
                                        <div class="d-flex align-items-center">
                                            <svg width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle me-2" viewBox="0 0 16 16">
                                                <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z" />
                                                <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z" />
                                            </svg>
                                            <h6 class="card-title mb-0">Terjadi Kesalahan</h6>
                                        </div>
                                        @foreach ($errors->all() as $error)
                                        <p class="card-text mt-2 mb-0">{{ $error }}</p>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Form -->
                                <form method="POST" action="{{ route('password.email') }}" class="row g-1 p-3 p-md-4">
                                    @csrf
                                    <div class="col-12 text-center mb-4">
                                        <h1>Lupa Sandi</h1>
                                        <span>Masukkan alamat email Anda dan kami akan mengirimkan link reset sandi</span>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Alamat Email</label>
                                            <input type="email" name="email" class="form-control form-control-lg" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                                            <div class="form-text text-light">Masukkan alamat email yang terdaftar</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 text-center mt-4">
                                        <button type="submit" class="btn btn-lg btn-block btn-light lift text-uppercase">
                                            Kirim Link Reset Sandi
                                        </button>
                                    </div>
                                    
                                    <div class="col-12 text-center mt-3">
                                        <a href="{{ route('login') }}" class="text-secondary">
                                            ← Kembali ke halaman login
                                        </a>
                                    </div>
                                </form>
                                <!-- End Form -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/bundles/libscripts.bundle.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const errorAlert = document.getElementById('errorAlert');
            const successAlert = document.getElementById('successAlert');

            // Auto hide alerts after 5 seconds
            if (errorAlert) {
                setTimeout(() => {
                    errorAlert.style.opacity = '0';
                    errorAlert.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        errorAlert.remove();
                    }, 500);
                }, 5000);
            }

            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.opacity = '0';
                    successAlert.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        successAlert.remove();
                    }, 500);
                }, 5000);
            }
        });
    </script>

</body>
</html>