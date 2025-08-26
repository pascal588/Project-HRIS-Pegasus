<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    // GET /api/divisions
    public function index()
    {
        return response()->json(Division::all(), 200);
    }

    // POST /api/divisions
    public function store(Request $request)
    {
        $request->validate([
            'nama_divisi' => 'required|string|max:45',
        ]);

        $division = Division::create($request->all());

        return response()->json($division, 201);
    }

    // GET /api/divisions/{id}
    public function show($id)
    {
        $division = Division::findOrFail($id);
        return response()->json($division, 200);
    }

    // PUT /api/divisions/{id}
    public function update(Request $request, $id)
    {
        $division = Division::findOrFail($id);

        $request->validate([
            'nama_divisi' => 'required|string|max:45',
        ]);

        $division->update($request->all());

        return response()->json($division, 200);
    }

    // DELETE /api/divisions/{id}
    public function destroy($id)
    {
        $division = Division::findOrFail($id);
        $division->delete();

        return response()->json(null, 204);
    }
}
