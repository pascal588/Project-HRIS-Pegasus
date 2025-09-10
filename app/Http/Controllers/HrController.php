<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HrController extends Controller
{
    // Di HrController atau controller yang sesuai
public function detailAbsensi($employee_id)
{
    return view('hr.detail-absensi', compact('employee_id'));
}
}
