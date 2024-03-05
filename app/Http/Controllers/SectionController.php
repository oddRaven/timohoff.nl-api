<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Section;
use App\Models\LanguageTranslation;
use App\Models\Translation;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $sections = DB::table('sections')
            ->join('language_translations AS translation', function (JoinClause $join) use ($language_code) {
                $join->on('sections.title_translation_id', '=', 'translation.translation_id')
                    ->where('translation.language_code', '=', $language_code);
            })
            ->select('sections.*', 'translation.text AS title')
            ->get();

        return response()->json($sections);
    }

    public function find (Request $request, $id)
    {
        $section = Section::find($id);

        $section->title_translations = DB::table('sections')
            ->join('language_translations AS translation', function (JoinClause $join) {
                $join->on('sections.title_translation_id', '=', 'translation.translation_id');
            })
            ->select('translation.language_code AS code', 'translation.text AS text')
            ->where('sections.id', $id)
            ->get();

        return response()->json($section);
    }

    public function store (Request $request)
    {
        $translation = new Translation;
        $translation->title = 'section title';
        $translation->save();

        foreach ($request->title_translations as $title_translation) {
            $language_translation = new LanguageTranslation;
            $language_translation->translation_id = $translation->id;
            $language_translation->language_code = $title_translation['code'];
            $language_translation->text = $title_translation['text'];
            $language_translation->save();
        }

        $section = new Section; 
        $section->order = $request->order ?? 0;
        $section->title_translation_id = $translation->id;
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

        foreach ($title_translation as $request->title_translations) {
            $language_translation = new LanguageTranslation;
            $language_translation->language_code = $title_translation->code;
            $language_translation->text = $title_translation->text;
            $language_translation->save();
        }

        $response = [
            "message" => "Section updated.",
            "section" => $section
        ];

        return response()->json($response);
    }

    public function delete (Request $request, $id)
    {
        $section = Section::find($id); 
        $section->delete();

        $response = [
            "message" => "Section deleted."
        ];

        return response()->json($response, 200);
    }
}