<?php

namespace App\Http\Controllers;

use App\Models\InspectionType;
use App\Models\InspectionHeader;
use App\Models\InspectionQuestion;
use Illuminate\Http\Request;
use NunoMaduro\Collision\Adapters\Laravel\Inspector;

class InspectionSetupController extends Controller
{
    public function index()
    {
        $inspectionTypes = InspectionType::latest()->get();
        return view('Admin.Question.index', compact('inspectionTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:inspection_types,name',
        ]);

        InspectionType::create(['name' => $request->name]);

        return redirect()->route('inspection-types.index')->with('success', 'Jenis inspeksi ditambahkan.');
    }
}
