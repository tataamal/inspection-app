<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function create_jenis_inspeksi()
    {
        return view('admin.create_type');
    }

    public function create_question()
    {
        return view('admin.create');
    }

    public function edit()
    {
        return view('admin.edit');
    }
}
