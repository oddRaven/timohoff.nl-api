<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Section;
use App\Models\LanguageTranslation;
use App\Models\Translation;

use App\Services\TranslationService;

class SectionController extends Controller
{
    private TranslationService $translation_service;

    public function __construct ()
    {
        $this->translation_service = new TranslationService;
    }

    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('sections');
        $query = $this->translation_service->join($query, 'sections', 'title_translation_id', 'translation', $language_code);
        $sections = $query->select('sections.*', 'translation.text AS title')
            ->get();

        return response()->json($sections);
    }

    public function show (Request $request, $id)
    {
        $section = Section::find($id);
        $section->title_translations = $this->translation_service->get('sections', 'title_translation_id', ['id' => $id]);

        return response()->json($section);
    }

    public function store (Request $request)
    {
        $section = new Section; 
        $section->order = $request->order ?? 0;
        $section->title_translation_id = $this->translation_service->store($request->title_translations, 'section title');
        $section->save();

        $response = [
            "message" => "Section created.",
            "section" => $section
        ];

        return response()->json($response, 201);
    }

    public function update (Request $request, $id)
    {
        $section = Section::find($id); 
        $section->order = $request->order;
        $section->save();

        $this->translation_service->update($request->title_translation_id, $request->title_translations);

        $response = [
            "message" => "Section updated.",
            "section" => $section
        ];

        return response()->json($response);
    }

    public function delete (Request $request, $id)
    {
        $section = Section::find($id);
        $section->destroy($id);

        Translation::destroy($section->title_translation_id);

        $response = [
            "message" => "Section deleted."
        ];

        return response()->json($response);
    }
}