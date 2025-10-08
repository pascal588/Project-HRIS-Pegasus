<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleApiController extends Controller
{
    // GET /api/roles
    // RoleApiController.php - Pastikan response konsisten
public function index()
{
    $roles = Role::with('division')->get(); // Tambahkan with division
    return response()->json([
        'success' => true, // Tambahkan success flag
        'data' => $roles
    ]);
}

public function store(Request $request)
{
    $request->validate([
        'nama_jabatan' => 'required|string|max:50',
        'division_id' => 'required|exists:divisions,id_divisi'
    ]);

    try {
        $role = Role::create([
            'nama_jabatan' => $request->nama_jabatan,
            'division_id' => $request->division_id
        ]);

        // Load division data untuk response
        $role->load('division');

        return response()->json([
            'success' => true,
            'message' => 'Jabatan berhasil ditambahkan',
            'data' => $role
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal menambahkan jabatan: ' . $e->getMessage()
        ], 500);
    }
}

// RoleApiController.php - tambahkan method destroy

public function destroy($id)
{
    try {
        $role = Role::findOrFail($id);
        
        // Cek jika role masih digunakan
        if ($role->employees()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus jabatan karena masih digunakan oleh karyawan'
            ], 400);
        }
        
        $role->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Jabatan berhasil dihapus'
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus jabatan: ' . $e->getMessage()
        ], 500);
    }
}
}
