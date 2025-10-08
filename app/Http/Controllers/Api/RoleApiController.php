<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoleApiController extends Controller
{
    /**
     * GET /api/roles - Get all roles with pagination and filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Parameters dengan default values
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            $divisiFilter = $request->get('divisi', '');
            $searchQuery = $request->get('search', '');

            // Optimized query - hanya select yang diperlukan
            $query = Role::with([
                'division:id_divisi,nama_divisi' // Hanya ambil kolom yang diperlukan
            ])->withCount('employees as jumlah_karyawan');

            // Apply filters
            if (!empty($divisiFilter)) {
                $query->where('division_id', $divisiFilter);
            }

            if (!empty($searchQuery)) {
                $query->where('nama_jabatan', 'like', '%' . $searchQuery . '%');
            }

            // Order by latest dan pagination
            $roles = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $roles->items(),
                'meta' => [
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                    'per_page' => $roles->perPage(),
                    'total' => $roles->total(),
                    'from' => $roles->firstItem(),
                    'to' => $roles->lastItem()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Role API Index Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data jabatan'
            ], 500);
        }
    }

    /**
     * POST /api/roles - Create new role
     */
    public function store(Request $request): JsonResponse
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'nama_jabatan' => 'required|string|max:50',
            'division_id' => 'required|exists:divisions,id_divisi',
            'status' => 'sometimes|in:Aktif,Non-Aktif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Cek duplikasi nama jabatan dalam divisi yang sama
            $existingRole = Role::where('nama_jabatan', $request->nama_jabatan)
                ->where('division_id', $request->division_id)
                ->first();

            if ($existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jabatan dengan nama tersebut sudah ada di divisi ini'
                ], 422);
            }

            // Create role
            $role = Role::create([
                'nama_jabatan' => $request->nama_jabatan,
                'division_id' => $request->division_id,
                'status' => $request->status ?? 'Aktif'
            ]);

            // Load relationships dalam single query
            $role->load(['division:id_divisi,nama_divisi']);
            $role->loadCount('employees as jumlah_karyawan');

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Jabatan berhasil dibuat'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role Store Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat jabatan'
            ], 500);
        }
    }

    /**
     * GET /api/roles/{id} - Get single role
     */
    public function show($id): JsonResponse
    {
        try {
            // Optimized query - hanya ambil kolom yang diperlukan
            $role = Role::with([
                'division:id_divisi,nama_divisi',
                'employees:id_karyawan,nama' // Jika perlu data karyawan
            ])->withCount('employees as jumlah_karyawan')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $role
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jabatan tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Role Show Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jabatan'
            ], 500);
        }
    }

    /**
     * PUT /api/roles/{id} - Update role
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_jabatan' => 'required|string|max:50',
                'division_id' => 'required|exists:divisions,id_divisi',
                'status' => 'required|in:Aktif,Non-Aktif'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek duplikasi (exclude current record)
            $existingRole = Role::where('nama_jabatan', $request->nama_jabatan)
                ->where('division_id', $request->division_id)
                ->where('id_jabatan', '!=', $id)
                ->first();

            if ($existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jabatan dengan nama tersebut sudah ada di divisi ini'
                ], 422);
            }

            DB::beginTransaction();

            // Update role
            $role->update([
                'nama_jabatan' => $request->nama_jabatan,
                'division_id' => $request->division_id,
                'status' => $request->status
            ]);

            // Load updated data dengan relationships
            $role->load(['division:id_divisi,nama_divisi']);
            $role->loadCount('employees as jumlah_karyawan');

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Jabatan berhasil diperbarui'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jabatan tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role Update Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jabatan'
            ], 500);
        }
    }

    /**
     * DELETE /api/roles/{id} - Delete role
     */
    public function destroy($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            // Check if role has employees
            $employeeCount = $role->employees()->count();
            if ($employeeCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak dapat menghapus jabatan karena masih digunakan oleh {$employeeCount} karyawan"
                ], 400);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jabatan berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jabatan tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Role Delete Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jabatan'
            ], 500);
        }
    }

    /**
     * GET /api/roles/list/all - Get all roles without pagination (for dropdowns)
     */
    public function listAll(Request $request): JsonResponse
    {
        try {
            $divisiFilter = $request->get('divisi', '');

            $query = Role::with(['division:id_divisi,nama_divisi'])
                ->where('status', 'Aktif');

            if (!empty($divisiFilter)) {
                $query->where('division_id', $divisiFilter);
            }

            $roles = $query->orderBy('nama_jabatan', 'asc')
                ->get(['id_jabatan', 'nama_jabatan', 'division_id']);

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            Log::error('Role ListAll Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data jabatan'
            ], 500);
        }
    }
}
