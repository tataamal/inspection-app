<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use function PHPUnit\Framework\returnArgument;

class InspectionController extends Controller
{
    public function Dashboard()
    {
        return view('dashboard');
    }

    public function ProSemarang()
    {
        return view('semarang.pro');
    }

    public function DOSemarang()
    {
        return view('semarang.do');
    }

    public function InspectionSemarang()
    {
        return view('semarang.inspection');
    }

    public function ActivitySemarang()
    {
        return view('semarang.activity');
    }
}
