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
    public function index()
    {
        $employees = Employee::with(['user', 'roles.division'])->get();
        return response()->json([
            'success' => true,
            'data' => $employees
        ], 200);
    }

    // POST /api/employees
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'no_telp' => 'required|string|max:15',
            'gender' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'email_verified_at' => now(),
            'role_id' => 'required|exists:roles,id_jabatan'
        ]);

        $employee = DB::transaction(function () use ($request) {
            // buat user baru
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // buat employee
            $employee = Employee::create([
                'user_id' => $user->id,
                'nama' => $request->nama,
                'no_telp' => $request->no_telp,
                'gender' => $request->gender,
            ]);

            // assign role
            $employee->roles()->attach($request->role_id);

            return $employee->load('user', 'roles.division');
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
            'role_id' => 'required|exists:roles,id_jabatan'
        ]);

        DB::transaction(function () use ($request, $employee) {
            $employee->update([
                'nama' => $request->nama,
                'no_telp' => $request->no_telp,
                'gender' => $request->gender,
            ]);

            $employee->user->update([
                'email' => $request->email,
            ]);

            $employee->roles()->sync([$request->role_id]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil diperbarui',
            'data' => $employee->load('user', 'roles.division')
        ], 200);
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
