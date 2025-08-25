@extends('template.template')

@section('title', 'edit profile') {{-- sesuaikan sama template kamu --}}

@section('content')
   
    <style>
        .profile-pic {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .profile-pic img {
            border-radius: 50%;
            border: 3px solid #ddd;
            object-fit: cover;
            transition: 0.3s;
        }
        .profile-pic:hover img {
            filter: brightness(70%);
        }
        .profile-pic .upload-icon {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: white;
            border-radius: 50%;
            padding: 5px;
            font-size: 14px;
        }
        .profile-pic input {
            display: none;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">

    <!-- Foto  Nama -->
    <div class="card p-4 mb-4 shadow">
        <div class="d-flex align-items-center">
            <div class="profile-pic me-4">
                <img src="https://via.placeholder.com/120" width="120" height="120" id="profilePreview">
                <span class="upload-icon"><i class="icofont-camera"></i></span>
                <input type="file" id="profileInput" accept="image/*">
            </div>
            <div>
                <h4 class="mb-1">Nama Karyawan</h4>
                <p class="text-muted mb-0">ID Karyawan: 123456</p>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <button class="btn btn-primary me-2" id="btnProfil">Profil</button>
        <button class="btn btn-outline-primary" id="btnPassword">Ganti Password</button>
    </div>

    <!-- Profil -->
    <div class="card p-4 shadow" id="profilCard">
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" class="form-control" value="email@contoh.com" disabled>
            </div>
            <div class="col-md-6">
                <label>No. Telepon</label>
                <input type="text" class="form-control" value="08123456789" disabled>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Gender</label>
                <input type="text" class="form-control" value="Perempuan" disabled>
            </div>
            <div class="col-md-6">
                <label>Jabatan</label>
                <input type="text" class="form-control" value="Staff IT" disabled>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Divisi</label>
                <input type="text" class="form-control" value="IT Department" disabled>
            </div>
        </div>
    </div>

    <!-- Ganti Password -->
    <div class="card p-4 shadow d-none" id="passwordCard">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="current_password" class="form-label">Password Lama</label>
            <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password">
            @error('current_password')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
            @error('password')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
            @error('password_confirmation')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Simpan Password</button>

        @if (session('status') === 'password-updated')
            <p class="text-success mt-2">Password berhasil diperbarui!</p>
        @endif
    </form>
</div>

</div>

<script>
    const profileInput = document.getElementById('profileInput');
    const profilePreview = document.getElementById('profilePreview');

    document.querySelector('.profile-pic').addEventListener('click', () => {
        profileInput.click();
    });

    profileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            profilePreview.src = URL.createObjectURL(file);
        }
    });

    const btnProfil = document.getElementById('btnProfil');
    const btnPassword = document.getElementById('btnPassword');
    const profilCard = document.getElementById('profilCard');
    const passwordCard = document.getElementById('passwordCard');

    btnProfil.addEventListener('click', () => {
        profilCard.classList.remove('d-none');
        passwordCard.classList.add('d-none');
        btnProfil.classList.add('btn-primary');
        btnProfil.classList.remove('btn-outline-primary');
        btnPassword.classList.add('btn-outline-primary');
        btnPassword.classList.remove('btn-primary');
    });

    btnPassword.addEventListener('click', () => {
        profilCard.classList.add('d-none');
        passwordCard.classList.remove('d-none');
        btnPassword.classList.add('btn-primary');
        btnPassword.classList.remove('btn-outline-primary');
        btnProfil.classList.add('btn-outline-primary');
        btnProfil.classList.remove('btn-primary');
    });
</script>

@endsection