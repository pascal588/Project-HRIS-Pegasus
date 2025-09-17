<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeeApiController extends Controller
{
    // GET /api/employees
    function index()
    { 
        $employees = Employee::with(['user', 'roles.division'])->get();

        // Tambahkan URL foto jika ada
        $employees->transform(function($employee) {
            if ($employee->foto) {
                $employee->foto_url = asset('storage/' . $employee->foto);
            } else {
                $employee->foto_url = asset('assets/images/default-avatar.png');
            }
            return $employee;
        });

        return response()->json([
            'success' => true,
            'data' => $employees
        ], 200);
    }

    public function kepalaDivisi()
    {
        try {
            $employees = Employee::whereHas('roles', function ($query) {
                $query->whereRaw('LOWER(nama_jabatan) = ?', ['kepala divisi']);
            })->with(['user', 'roles.division'])->get();

            return response()->json([
                'success' => true,
                'data' => $employees
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ], 500);
        }
    }

    // Di method store(), ubah validasi password
    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|integer|unique:employees,id_karyawan',
            'nama' => 'required|string|max:100',
            'no_telp' => 'required|string|max:15',
            'gender' => 'required',
            'email' => 'required|email|unique:users,email',
        ]);

        $employee = DB::transaction(function () use ($request) {
            // buat user baru dengan password default
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make('12345678'), // Password default
            ]);

            // buat employee
            $employee = Employee::create([
                'id_karyawan' => $request->id_karyawan,
                'user_id' => $user->id,
                'nama' => $request->nama,
                'no_telp' => $request->no_telp,
                'gender' => $request->gender,
                'status' => 'Aktif', // default status
            ]);

            // cari role default "Karyawan"
            if ($request->filled('role_id')) {
                $employee->roles()->attach($request->role_id);
            } else {
                // fallback: cari role Karyawan di divisi yang dikirim
                if ($request->filled('division_id')) {
                    $defaultRole = Role::where('nama_jabatan', 'Karyawan')
                        ->where('division_id', $request->division_id)
                        ->first();

                    if ($defaultRole) {
                        $employee->roles()->attach($defaultRole->id_jabatan);
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil ditambahkan',
            'data' => $employee
        ], 201);
    }

    // PUT /api/employees/{id}
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'no_telp' => 'required|string|max:15',
            'gender' => 'required',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'status' => 'required|in:Aktif,Non-Aktif,Cuti'
            // Hapus validasi role_ids karena tidak dikirim dari form edit biasa
        ]);

        DB::transaction(function () use ($request, $employee) {
            $employee->update([
                'nama' => $request->nama,
                'no_telp' => $request->no_telp,
                'gender' => $request->gender,
                'status' => $request->status, // PASTIKAN INI ADA
            ]);

            $employee->user->update([
                'email' => $request->email,
            ]);

            // HAPUS BARIS INI: $employee->roles()->sync($request->role_ids);
            // Karena role_ids tidak dikirim dari form edit biasa
        });

        // Reload data dengan division information
        $employee->load(['user', 'roles.division']);

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil diperbarui',
            'data' => $employee
        ], 200);
    }

    // POST /api/employees/{employee}/roles - UPDATE ROLES SAJA
    public function updateRoles(Request $request, Employee $employee)
    {
        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id_jabatan'
        ]);

        try {
            DB::transaction(function () use ($request, $employee) {
                // Sync multiple roles
                $employee->roles()->sync($request->role_ids);
            });

            // Reload data dengan division information
            $employee->load(['user', 'roles.division']);

            return response()->json([
                'success' => true,
                'message' => 'Jabatan karyawan berhasil diperbarui',
                'data' => $employee
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jabatan: ' . $e->getMessage()
            ], 500);
        }
    }

    // DELETE /api/employees/{id}
    public function destroy(Employee $employee)
    {
        DB::transaction(function () use ($employee) {
            // hapus user terkait
            $employee->user()->delete();
            // hapus employee
            $employee->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Karyawan & user berhasil dihapus'
        ], 200);
    }
}
