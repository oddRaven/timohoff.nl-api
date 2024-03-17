<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Article;
use App\Models\SectionItem;
use App\Models\LanguageTranslation;
use App\Models\ProfileCollection;
use App\Models\Translation;

use App\Services\TranslationService;

class SectionItemController extends Controller
{
    private TranslationService $translation_service;

    public function __construct ()
    {
        $this->translation_service = new TranslationService;
    }

    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('section_items');
        $query = $this->translation_service->join($query, 'section_items', 'title_translation_id', 'translation', $language_code);
        $query = $query->select('section_items.*', 'translation.text AS title');
            
        if ($request->has('section_id')) {
            $query = $query->where('section_id', $request->query('section_id'));
        }

        $section_items = $query->get();

        return response()->json($section_items);
    }

    public function show (Request $request, $type, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $section_item = DB::table('section_items')
            ->select('section_items.*')
            ->where(['section_items.item_id' => $id, 'section_items.item_type' => $type])
            ->first();

        /*$section_item->item = DB::table($section_item->item_type)
            ->where('id', $id)
            ->first();*/

        $identifiers = ['item_id' => $id, 'item_type' => $type];
        $section_item->title_translations = $this->translation_service->get('section_items', 'title_translation_id', $identifiers);

        return response()->json($section_item);
    }

    public function store (Request $request)
    {
        $section_item = new SectionItem; 
        $section_item->item_id = $request->item_id;
        $section_item->item_type = $request->item_type;
        $section_item->section_id = $request->section_id;
        $section_item->order = $request->order ?? 0;
        $section_item->title_translation_id = $this->translation_service->store($request->title_translations, 'section item title');
        $section_item->save();

        $response = [
            "message" => "Section item created.",
            "sectionItem" => $section_item
        ];

        return response()->json($response, 201);
    }

    public function update (Request $request, $type, $id)
    {
        SectionItem::where('item_id', '=', $id, 'and')
            ->where('item_type', '=', $type)
            ->update(['order' => $request->order]);

        $this->translation_service->update($request->title_translation_id, $request->title_translations);

        $section_item = SectionItem::where('item_id', '=', $id, 'and')
            ->where('item_type', '=', $type)
            ->first();

        $response = [
            "message" => "Section item updated.",
            "sectionItem" => $section_item
        ];

        return response()->json($response);
    }

    public function delete (Request $request, $type, $id)
    {
        DB::table($type)->delete($id);

        $timeline = SectionItem::where(['item_id' => $id, 'item_type' => $type])
            ->first();

        Translation::destroy($timeline->title_translation_id);

        SectionItem::where(['item_id' => $id, 'item_type' => $type])
            ->delete();

        $response = [
            "message" => "Section item deleted."
        ];

        return response()->json($response);
    }
}