<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Translation;
use App\Services\TranslationService;

class PhaseController extends Controller
{
    private TranslationService $translation_service;

    public function __construct ()
    {
        $this->translation_service = new TranslationService;
    }

    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('phases');
        $query = $this->translation_service->join($query, 'phases', 'title_translation_id', 'translation', $language_code);

        if ($request->has('timeline_id')) {
            $query = $query->where('phases.timeline_id', $request->query('timeline_id'));
        }

        $phases = $query->select('phases.id', 'phases.timeline_id', 'phases.color', 'translation.text AS title')
            ->get();

        return response()->json($phases);
    }

    public function show(Request $request, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('phases');
        $query = $this->translation_service->join($query, 'phases', 'title_translation_id', 'translation', $language_code);
        $phase = $query->select('phases.id', 'phases.timeline_id', 'phases.color', 'translation.text AS title')
            ->where('phases.id', $id)
            ->first();

        if ($request->has('include_language_translations') && $phase !== null) {
            $phase->title_translations = $this->translation_service->get('phases', 'title_translation_id', ['id' => $id]);
        }

        return response()->json($phase);
    }

    public function store(Request $request)
    {
        $title_translation_id = $this->translation_service->store($request->title_translations, 'phase title');

        $phase_id = DB::table('phases')->insertGetId([
            'timeline_id' => $request->timeline_id,
            'title_translation_id' => $title_translation_id,
            'color' => $request->color,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $phase = DB::table('phases')->find($phase_id);

        $response = [
            "message" => "Phase created.",
            "phase" => $phase
        ];

        return response()->json($response, 201);
    }

    public function update(Request $request, $id)
    {
        $phase = DB::table('phases')->where('id', $id)->first();

        DB::table('phases')
            ->where('id', $id)
            ->update([
                'timeline_id' => $request->timeline_id,
                'color' => $request->color,
                'updated_at' => now()
            ]);

        if ($phase !== null) {
            $this->translation_service->update($phase->title_translation_id, $request->title_translations);
        }

        $updated_phase = DB::table('phases')->where('id', $id)->first();

        $response = [
            "message" => "Phase updated.",
            "phase" => $updated_phase
        ];

        return response()->json($response);
    }

    public function destroy(Request $request, $id)
    {
        $phase = DB::table('phases')->where('id', $id)->first();

        DB::table('phases')->where('id', $id)->delete();

        if ($phase !== null) {
            Translation::destroy($phase->title_translation_id);
        }

        $response = [
            "message" => "Phase deleted."
        ];

        return response()->json($response);
    }
}
