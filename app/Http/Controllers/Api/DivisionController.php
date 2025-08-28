<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    // GET semua divisi
    public function index()
    {
        $divisions = Division::with('roles.roles')->get();

        $data = $divisions->map(function($division) {
            $jumlah_karyawan = $division->roles->flatMap(function($role){
                return $role->roles;
            })->count();

            return [
                'id_divisi' => $division->id_divisi,
                'nama_divisi' => $division->nama_divisi,
                'jumlah_karyawan' => $jumlah_karyawan
            ];
        });

        return response()->json($data);
    }

    // POST tambah divisi
    public function store(Request $request)
    {
        $request->validate([
            'id_divisi' => 'required|integer|unique:divisions,id_divisi',
            'nama_divisi' => 'required|string|max:45',
        ]);

        $division = Division::create([
            'id_divisi' => $request->id_divisi,
            'nama_divisi' => $request->nama_divisi,
        ]);

        return response()->json([
            'message' => 'Divisi berhasil ditambahkan',
            'data' => $division
        ]);
    }

    // GET detail divisi
    public function show($id)
    {
        $division = Division::findOrFail($id);
        return response()->json($division);
    }

    // PUT update divisi (id_divisi bisa ikut diubah)
    public function update(Request $request, $id)
    {
        $division = Division::findOrFail($id);

        $request->validate([
            'id_divisi' => 'required|integer|unique:divisions,id_divisi,' . $division->id_divisi . ',id_divisi',
            'nama_divisi' => 'required|string|max:45',
        ]);

        $division->update([
            'id_divisi' => $request->id_divisi,
            'nama_divisi' => $request->nama_divisi,
        ]);

        return response()->json([
            'message' => 'Divisi berhasil diperbarui',
            'data' => $division
        ]);
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
