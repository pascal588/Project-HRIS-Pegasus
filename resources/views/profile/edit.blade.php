@extends('template.template')

@section('title', 'Edit Profil')

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
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .spinner-border {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>

<div class="container py-5">

    <!-- Foto dan Nama -->
    <div class="card p-4 mb-4 shadow">
        <div class="d-flex align-items-center">
            <div class="profile-pic me-4" id="profilePicContainer">
                <img src="{{ $user->employee->foto ? asset('storage/' . $user->employee->foto) : 'https://via.placeholder.com/120' }}" 
                     width="120" height="120" id="profilePreview">
                <span class="upload-icon"><i class="icofont-camera"></i></span>
                <input type="file" id="profileInput" accept="image/*">
                
                <!-- Loading Indicator -->
                <div class="spinner-border text-primary" role="status" style="display: none;" id="loadingSpinner">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div>
                <h4 class="mb-1">{{ $user->employee->nama }}</h4>
                <p class="text-muted mb-0">ID Karyawan: {{ $user->employee->id_karyawan }}</p>
                
                <!-- Status Message -->
                <div id="photoStatus" class="mt-2"></div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <button class="btn btn-primary me-2" id="btnProfil">Profil</button>
        <button class="btn btn-outline-primary" id="btnPassword">Ganti Password</button>
    </div>

    <!-- Profil (hanya tampilan, tidak bisa diubah) -->
    <div class="card p-4 shadow" id="profilCard">
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" class="form-control" value="{{ $user->email }}" disabled>
            </div>
            <div class="col-md-6">
                <label>No. Telepon</label>
                <input type="text" class="form-control" value="{{ $user->employee->no_telp }}" disabled>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Gender</label>
                <input type="text" class="form-control" value="{{ $user->employee->gender }}" disabled>
            </div>
            <div class="col-md-6">
                <label>Jabatan</label>
                <input type="text" class="form-control" value="{{ $user->employee->roles->first()->nama_jabatan ?? '-' }}" disabled>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Divisi</label>
                <input type="text" class="form-control" value="{{ $user->employee->roles->first()->division->nama_divisi ?? '-' }}" disabled>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    console.log('Profile edit script loaded');

    const profileInput = document.getElementById('profileInput');
    const profilePreview = document.getElementById('profilePreview');
    const profilePicContainer = document.getElementById('profilePicContainer');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const photoStatus = document.getElementById('photoStatus');

    const csrfToken = '{{ csrf_token() }}';
    const updatePhotoUrl = '{{ route("profile.update.photo") }}';

    console.log('CSRF Token:', csrfToken ? 'Exists' : 'Missing');
    console.log('Update Photo URL:', updatePhotoUrl);

    document.querySelector('.profile-pic').addEventListener('click', () => {
        console.log('Profile pic clicked');
        profileInput.click();
    });

    profileInput.addEventListener('change', (e) => {
        
        const file = e.target.files[0];
        
        if (file) {
            console.log('File details:', {
                name: file.name,
                size: file.size,
                type: file.type
            });

            if (!file.type.match('image.*')) {
                showMessage('Hanya file gambar yang diizinkan!', 'danger');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                showMessage('Ukuran file maksimal 5MB!', 'danger');
                return;
            }

            // console.log('Creating preview');
            profilePreview.src = URL.createObjectURL(file);
            
            // console.log('Starting upload...');
            uploadPhoto(file);
        }
    });

    function uploadPhoto(file) {
        console.log('uploadPhoto function called');
        
        loadingSpinner.style.display = 'block';
        profilePicContainer.classList.add('loading');
        showMessage('Mengupload foto...', 'info');

        const formData = new FormData();
        formData.append('foto', file);
        formData.append('_token', csrfToken);
        formData.append('_method', 'PATCH');

        console.log('FormData created, making AJAX request...');

        $.ajax({
            url: updatePhotoUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false, 
            success: function(response) {
                console.log('✅ SUCCESS Response:', response);
                
                if (response.success) {
                    // Update preview dengan cache busting
                    const newImageUrl = '{{ asset("storage") }}/' + response.new_path + '?t=' + new Date().getTime();
                    profilePreview.src = newImageUrl;
                    
                    showMessage(response.message, 'success');
                    
                    profileInput.value = '';
                } else {
                    showMessage(response.message || 'Terjadi kesalahan', 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.log('❌ ERROR:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                let message = 'Terjadi kesalahan saat upload foto';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    message = errors.foto ? errors.foto[0] : 'Data tidak valid';
                } else if (xhr.status === 0) {
                    message = 'Tidak dapat terhubung ke server';
                } else if (xhr.status === 500) {
                    message = 'Terjadi kesalahan server';
                }
                
                showMessage(message, 'danger');
                
                resetPreview();
            },
            complete: function() {
                console.log('AJAX request complete');
                loadingSpinner.style.display = 'none';
                profilePicContainer.classList.remove('loading');
            }
        });
    }

    function resetPreview() {
        const originalPhoto = '{{ $user->employee->foto ? asset("storage/" . $user->employee->foto) : "https://via.placeholder.com/120" }}';
        profilePreview.src = originalPhoto + '?t=' + new Date().getTime();
    }

    function showMessage(message, type) {
        console.log('Show message:', type, message);
        photoStatus.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        
        if (type === 'success') {
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 3000);
        }
    }

    // Tab switching functionality
    const btnProfil = document.getElementById('btnProfil');
    const btnPassword = document.getElementById('btnPassword');
    const profilCard = document.getElementById('profilCard');
    const passwordCard = document.getElementById('passwordCard');

    if (btnProfil && btnPassword) {
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
    }

    console.log('Script initialization complete');
</script>

@endsection