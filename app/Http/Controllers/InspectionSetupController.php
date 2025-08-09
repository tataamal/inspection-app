<?php

namespace App\Http\Controllers;
use App\Models\QualityInspectionLot;
use App\Models\InspectionType;
use App\Models\InspectionHeader;
use App\Models\InspectionQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use NunoMaduro\Collision\Adapters\Laravel\Inspector;

class InspectionSetupController extends Controller
{
     public function Dashboard()
    {
        $totalInspection = QualityInspectionLot::count();
        return view('dashboard', compact('totalInspection'));
    }

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

    public function destroy($id)
    {
        $type = InspectionType::findOrFail($id);
        $type->delete();

        return redirect()->route('inspection-types.index')->with('success', 'Jenis inspeksi berhasil dihapus.');
    }

    public function create($id)
    {
        $inspectionType = InspectionType::with('headers.questions')->findOrFail($id);

        return view('Admin.Question.edit', compact('inspectionType'));
    }

    public function submitDetail(Request $request)
    {
        $data = $request->validate([
            'inspection_type_id' => 'required|exists:inspection_types,id',
            'headers' => 'required|array',
            'headers.*.title' => 'required|string',
            'headers.*.questions' => 'nullable|array',
            'headers.*.questions.*.text' => 'nullable|string',
        ]);

        foreach ($data['headers'] as $headerData) {
                $title = $headerData['title'] ?? null; // aman dari Undefined array key
                if (!$title) {
                    continue; // skip kalau kosong
                }

                $header = InspectionHeader::create([
                    'inspection_type_id' => $data['inspection_type_id'],
                    'title' => $title,
                ]);

                if (!empty($headerData['questions'])) {
                    foreach ($headerData['questions'] as $questionData) {
                        if (!empty($questionData['text'])) {
                            InspectionQuestion::create([
                                'header_id' => $header->id,
                                'question_text' => $questionData['text'],
                            ]);
                        }
                    }
                }
            }

            return redirect()->route('inspection-view')->with('success', 'Detail inspeksi berhasil disimpan.');
        }

    public function view()
    {
        $inspectionTypes = InspectionType::with([
            'headers.questions'
        ])->get();
        
        return view('Admin.Question.view', compact('inspectionTypes'));
    }
}