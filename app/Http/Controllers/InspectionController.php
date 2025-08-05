<?php

namespace App\Http\Controllers;
use App\Models\QualityInspectionLot;
use Illuminate\Http\Request;
use function PHPUnit\Framework\returnArgument;

class InspectionController extends Controller
{
    public function Dashboard()
    {
        $totalInspection = QualityInspectionLot::count();
        return view('dashboard', compact('totalInspection'));
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
