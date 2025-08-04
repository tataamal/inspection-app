<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function create()
    {
        return view('admin.create');
    }

    public function edit()
    {
        return view('admin.edit');
    }
}
