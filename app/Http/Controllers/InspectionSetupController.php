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

    public function create()
    {
        $inspectionTypes = InspectionType::latest()->get();
        return view('Admin.Question.create', compact('inspectionTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:inspection_types,name',
        ]);

        InspectionType::create(['name' => $request->name]);

        return redirect()->route('inspection-types.index')->with('success', 'Jenis inspeksi ditambahkan.');
    }

    public function updateJenisInspeksi(Request $request, $id)
    {
        $inspectionType = InspectionType::findOrFail($id);
        
        $request->validate([
            'name' => 'required|unique:inspection_types,name,' . $id,
        ]);

        $inspectionType->update(['name' => $request->name]);

        return redirect()->route('inspection-types.create')->with('success', 'Jenis inspeksi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $type = InspectionType::findOrFail($id);
        $type->delete();

        return redirect()->route('inspection-types.index')->with('success', 'Jenis inspeksi berhasil dihapus.');
    }

    public function edit($id)
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

        // dd($data);

        // Get existing headers
        $existingHeaders = InspectionHeader::where('inspection_type_id', $data['inspection_type_id'])
            ->with('questions')
            ->get()
            ->keyBy('id');

        $processedHeaderIds = [];

        foreach ($data['headers'] as $headerData) {
            $title = $headerData['title'] ?? null;
            if (!$title) {
                continue;
            }

            // Cari header berdasarkan title atau ID jika ada
            $headerId = $headerData['id'] ?? null;
            $header = null;

            if ($headerId && $existingHeaders->has($headerId)) {
                // Update existing header
                $header = $existingHeaders->get($headerId);
                $header->update(['title' => $title]);
            } else {
                // Cari berdasarkan title atau buat baru
                $header = InspectionHeader::updateOrCreate([
                    'inspection_type_id' => $data['inspection_type_id'],
                    'title' => $title,
                ]);
            }

            $processedHeaderIds[] = $header->id;

            // Handle questions
            $existingQuestions = $header->questions->keyBy('id');
            $processedQuestionIds = [];

            if (!empty($headerData['questions'])) {
                foreach ($headerData['questions'] as $questionData) {
                    if (!empty($questionData['text'])) {
                        $questionId = $questionData['id'] ?? null;
                        
                        if ($questionId && $existingQuestions->has($questionId)) {
                            // Update existing question
                            $question = $existingQuestions->get($questionId);
                            $question->update(['question_text' => $questionData['text']]);
                            $processedQuestionIds[] = $questionId;
                        } else {
                            // Create new question
                            $question = InspectionQuestion::create([
                                'header_id' => $header->id,
                                'question_text' => $questionData['text'],
                            ]);
                            $processedQuestionIds[] = $question->id;
                        }
                    }
                }
            }

            // Delete questions yang tidak ada di form
            InspectionQuestion::where('header_id', $header->id)
                ->whereNotIn('id', $processedQuestionIds)
                ->delete();
        }

        // Delete headers yang tidak ada di form
        InspectionHeader::where('inspection_type_id', $data['inspection_type_id'])
            ->whereNotIn('id', $processedHeaderIds)
            ->delete();
            
        
        return redirect()->route('inspection-types.index')->with('success', 'Detail inspeksi berhasil disimpan.');
    }

    public function view()
    {
        $inspectionTypes = InspectionType::with([
            'headers.questions'
        ])->get();
        
        return view('Admin.Question.view', compact('inspectionTypes'));
    }
}