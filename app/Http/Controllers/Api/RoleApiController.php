<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleApiController extends Controller
{
    // GET /api/roles
    public function index()
    {
        $roles = Role::all(); // ambil semua data jabatan
        return response()->json([
            'data' => $roles
        ]);
    }
}
