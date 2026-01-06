<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Employee; // Tambahkan ini
use App\Models\Role;     // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Tambahkan ini
use Illuminate\Support\Facades\Log; // Tambahkan ini

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::with(['roles.employees', 'roles' => function($query) {
            $query->where('nama_jabatan', 'Kepala Divisi');
        }])->get();

        $data = $divisions->map(function($division) {
            // Hitung jumlah karyawan UNIK per divisi
            $employeeCount = Employee::select(DB::raw('COUNT(DISTINCT employees.id_karyawan) as count'))
                ->join('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
                ->join('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
                ->where('roles.division_id', $division->id_divisi)
                ->value('count') ?? 0;

            // Cari kepala divisi
            $kepala = $division->roles->flatMap(fn($role) => $role->employees)->first();
            $kepala_nama = $kepala?->nama ?? '-';

            return [
                'id' => $division->id_divisi, // PASTIKAN ini id_divisi
                'id_divisi' => $division->id_divisi, // TAMBAHKAN field ini untuk kejelasan
                'kode_divisi' => $division->kode_divisi,
                'nama_divisi' => $division->nama_divisi, 
                'jumlah_karyawan' => $employeeCount,
                'kepala_divisi' => $kepala_nama
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_divisi' => 'required|string|max:20|unique:divisions,kode_divisi',
            'nama_divisi' => 'required|string|max:45',
        ]);

        try {
            // ID DIVISI AUTO GENERATE, tidak perlu diinput user
            $division = Division::create([
                'kode_divisi' => $request->kode_divisi,
                'nama_divisi' => $request->nama_divisi,
                // id_divisi otomatis dari auto-increment
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Divisi berhasil ditambahkan',
                'data' => $division
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating division: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan divisi: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET detail divisi
    public function show($id)
    {
        $division = Division::findOrFail($id);
        return response()->json($division);
    }

    public function update(Request $request, $id)
    {
        $division = Division::findOrFail($id);

        $request->validate([
            'kode_divisi' => 'required|string|max:20|unique:divisions,kode_divisi,' . $division->id_divisi . ',id_divisi',
            'nama_divisi' => 'required|string|max:45',
        ]);

        $division->update([
            'kode_divisi' => $request->kode_divisi,
            'nama_divisi' => $request->nama_divisi,
            // id_divisi TIDAK DIUPDATE (auto-increment tetap sama)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Divisi berhasil diperbarui',
            'data' => $division
        ]);
    }

    // DivisionController.php - Tambahkan method ini

// DivisionController.php - Tambahkan method getEmployees yang lebih sederhana

// GET karyawan by divisi
public function getEmployees($divisionId)
{
    try {
        Log::info("Mengambil karyawan untuk divisi: $divisionId");
        
        // Pastikan divisi ada
        $division = Division::find($divisionId);
        
        if (!$division) {
            return response()->json([
                'success' => false,
                'message' => 'Divisi tidak ditemukan'
            ], 404);
        }
        
        // Dapatkan semua karyawan yang memiliki role dalam divisi ini
        // Gunakan distinct() untuk menghindari duplikat
        $employees = Employee::select('employees.*')
            ->join('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
            ->join('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
            ->where('roles.division_id', $divisionId)
            ->with(['roles' => function($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            }])
            ->distinct('employees.id_karyawan') // Tambahkan distinct
            ->get();
        
        Log::info("Employees found: " . $employees->count());
        
        // Format data response
        $formattedEmployees = $employees->map(function($employee) {
            return [
                'id_karyawan' => $employee->id_karyawan,
                'nama' => $employee->nama,
                'roles' => $employee->roles->map(function($role) {
                    return [
                        'id_jabatan' => $role->id_jabatan,
                        'nama_jabatan' => $role->nama_jabatan
                    ];
                })
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $formattedEmployees
        ]);
        
    } catch (\Exception $e) {
        Log::error("Error getEmployees: " . $e->getMessage());
        Log::error("Trace: " . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal memuat data karyawan: ' . $e->getMessage()
        ], 500);
    }
}

// PUT update kepala divisi
public function updateHead(Request $request, $divisionId)
{
    try {
        $request->validate([
            'employee_id' => 'required|exists:employees,id_karyawan',
            'is_head' => 'required|boolean'
        ]);
        
        $division = Division::findOrFail($divisionId);
        
        // Cari role Kepala Divisi untuk divisi ini
        $headRole = Role::where('division_id', $divisionId)
                       ->where('nama_jabatan', 'Kepala Divisi')
                       ->first();
        
        if (!$headRole) {
            // Jika role Kepala Divisi belum ada, buat baru
            $headRole = Role::create([
                'nama_jabatan' => 'Kepala Divisi',
                'division_id' => $divisionId
            ]);
        }
        
        if ($request->is_head) {
            // Hapus kepala divisi sebelumnya
            DB::table('roles_has_employees')
                ->where('role_id', $headRole->id_jabatan)
                ->delete();
                
            // Tetapkan karyawan baru sebagai kepala divisi
            DB::table('roles_has_employees')->insert([
                'role_id' => $headRole->id_jabatan,
                'employee_id' => $request->employee_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $message = 'Karyawan berhasil ditetapkan sebagai kepala divisi';
        } else {
            // Hapus status kepala divisi
            DB::table('roles_has_employees')
                ->where('role_id', $headRole->id_jabatan)
                ->where('employee_id', $request->employee_id)
                ->delete();
                
            $message = 'Status kepala divisi berhasil dihapus';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui kepala divisi: ' . $e->getMessage()
        ], 500);
    }
}
// GET jumlah karyawan by divisi - VERSI DIPERBAIKI
public function getEmployeeCount($divisionId)
{
    try {
        // Gunakan cara yang sama dengan getGenderData() untuk konsistensi
        $employeeCount = Employee::select(DB::raw('COUNT(DISTINCT employees.id_karyawan) as count'))
            ->join('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
            ->join('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
            ->where('roles.division_id', $divisionId)
            ->value('count');

        return response()->json([
            'success' => true,
            'data' => [
                'total_employees' => $employeeCount
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal memuat jumlah karyawan: ' . $e->getMessage()
        ], 500);
    }
}
// GET data gender karyawan by divisi
public function getGenderData($divisionId)
{
    try {
        $genderData = Employee::select('gender', DB::raw('COUNT(DISTINCT employees.id_karyawan) as count'))
            ->join('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
            ->join('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
            ->where('roles.division_id', $divisionId)
            ->groupBy('gender')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $genderData
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal memuat data gender: ' . $e->getMessage()
        ], 500);
    }
}

    // DELETE hapus divisi
    public function destroy($id)
    {
        $division = Division::findOrFail($id);
        $division->forceDelete(); // hard delete
        // $division->delete(); // soft delete (Data kesimpen di database)
        return response()->json(['message' => 'Division deleted']);
    }
}