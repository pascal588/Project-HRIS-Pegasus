<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PenilaiController extends Controller
{
    public function listKaryawan()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return view('penilai.list-karyawan', [
                    'divisionName' => 'Tidak diketahui',
                    'employees' => []
                ]);
            }
            
            $employee = $user->employee;
            
            if (!$employee) {
                return view('penilai.list-karyawan', [
                    'divisionName' => 'Tidak diketahui',
                    'employees' => []
                ]);
            }
            
            // Load roles dengan division
            $employee->load(['roles.division']);
            
            // Dapatkan divisi dari employee
            $divisionIds = $employee->roles->pluck('division_id')->filter()->unique();
            
            if ($divisionIds->isEmpty()) {
                return view('penilai.list-karyawan', [
                    'divisionName' => 'Tidak diketahui',
                    'employees' => []
                ]);
            }
            
            // Jika punya lebih dari satu divisi, ambil yang pertama
            $divisionId = $divisionIds->first();
            
            // Dapatkan divisi
            $division = Division::find($divisionId);
            
            // Dapatkan semua karyawan yang memiliki role dalam divisi ini
            $employees = Employee::select('employees.*')
            ->join('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
            ->join('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
            ->where('roles.division_id', $divisionId)
            ->with(['user', 'roles' => function($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            }])
            ->distinct()
            ->get();
            
            return view('penilai.list-karyawan', [
                'divisionName' => $division ? $division->nama_divisi : 'Tidak diketahui',
                'employees' => $employees
            ]);        

        } catch (\Exception $e) {
            Log::error("Error in listKaryawan: " . $e->getMessage());
            
            return view('penilai.list-karyawan', [
                'divisionName' => 'Tidak diketahui',
                'employees' => []
            ]);
        }
    }
}