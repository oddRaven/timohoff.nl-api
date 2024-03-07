<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Article;
use App\Models\SectionItem;
use App\Models\LanguageTranslation;
use App\Models\Translation;

class SectionItemController extends Controller
{
    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('section_items')
            ->join('language_translations AS translation', function (JoinClause $join) use ($language_code) {
                $join->on('section_items.title_translation_id', '=', 'translation.translation_id')
                    ->where('translation.language_code', '=', $language_code);
            })
            ->select('section_items.*', 'translation.text AS title');
            
        if ($request->has('section_id')) {
            $query = $query->where('section_id', $request->query('section_id'));
        }

        $section_items = $query->get();

        return response()->json($section_items);
    }

    public function find (Request $request, $type, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $section_item = DB::table('section_items')
            ->select('section_items.*')
            ->where(['section_items.item_id' => $id, 'section_items.item_type' => $type])
            ->first();

        /*$section_item->item = DB::table($section_item->item_type)
            ->where('id', $id)
            ->first();*/

        $section_item->title_translations = DB::table('section_items')
            ->join('language_translations AS translation', function (JoinClause $join) {
                $join->on('section_items.title_translation_id', '=', 'translation.translation_id');
            })
            ->select('translation.language_code AS code', 'translation.text AS text')
            ->where(['section_items.item_id' => $id, 'section_items.item_type' => $type])
            ->get();

        return response()->json($section_item);
    }

    public function store (Request $request)
    {
        $translation = new Translation;
        $translation->title = 'section item title';
        $translation->save();

        foreach ($request->title_translations as $title_translation) {
            $language_translation = new LanguageTranslation;
            $language_translation->translation_id = $translation->id;
            $language_translation->language_code = $title_translation['code'];
            $language_translation->text = $title_translation['text'];
            $language_translation->save();
        }

        $section_item = new SectionItem; 
        $section_item->item_id = $request->item_id;
        $section_item->item_type = $request->item_type;
        $section_item->section_id = $request->section_id;
        $section_item->order = $request->order ?? 0;
        $section_item->title_translation_id = $translation->id;
        $section_item->save();

        $response = [
            "message" => "Section item created.",
            "sectionItem" => $section_item
        ];

        return response()->json($response, 201);
    }

    public function update (Request $request, $type, $id)
    {
        $section_item = SectionItem::where('item_id', '=', $id, 'and')->where('item_type', '=', $type); 
        $section_item->order = $request->order;
        $section_item->save();

        foreach ($title_translation as $request->title_translations) {
            $language_translation = new LanguageTranslation;
            $language_translation->language_code = $title_translation->code;
            $language_translation->text = $title_translation->text;
            $language_translation->save();
        }

        $response = [
            "message" => "Section item updated.",
            "sectionItem" => $section_item
        ];

        return response()->json($response);
    }

    public function delete (Request $request, $type, $id)
    {
        $section_item = SectionItem::where('item_id', '=', $id, 'and')->where('item_type', '=', $type); 
        $section_item->delete();

        if ($type == 'Articles') {
            Article::destroy($id);
        }

        $response = [
            "message" => "Section item deleted."
        ];

        return response()->json($response, 200);
    }
}
