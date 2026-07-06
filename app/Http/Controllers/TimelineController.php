<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Timeline;
use App\Models\Waypoint;
use App\Models\Translation;
use App\Services\TranslationService;

class TimelineController extends Controller
{
    private TranslationService $translation_service;

    public function __construct ()
    {
        $this->translation_service = new TranslationService;
    }

    public function index(Request $request)
    {
        $timeline = Waypoint::all();

        return response()->json($timeline);
    }

    public function show(Request $request, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('timelines');

        $query = $this->translation_service->join($query, 'timelines', 'title_translation_id', 'titleTranslation', $language_code);

        $timeline = $query->select('timelines.id', 'titleTranslation.text AS title')
            ->find($id);

        if ($request->has('include_language_translations')) {
            $timeline->title_translations = $this->translation_service->get('timelines', 'title_translation_id', ['id' => $id]);
        }

        if ($request->has('include_phases')) {
            $query = DB::table('phases');
            $query = $this->translation_service->join($query, 'phases', 'title_translation_id', 'translation', $language_code);
            $timeline->phases = $query->select('phases.id', 'phases.timeline_id', 'phases.color', 'translation.text AS title')
                ->where('phases.timeline_id', $id)
                ->get();

            if ($request->has('include_waypoints')) {
                foreach($timeline->phases as $phase){
                    $query = DB::table('waypoints');
                    $query = $this->translation_service->join($query, 'waypoints', 'title_translation_id', 'translation', $language_code);
                    $phase->waypoints = $query->select('waypoints.*', 'translation.text AS title')
                        ->where('waypoints.phase_id', $phase->id)
                        ->get();

                    foreach($phase->waypoints as $waypoint){
                        if ($waypoint->is_bound) {
                            $waypoint->color = $phase->color;
                        }
                    }
                }
            }
        }

        return response()->json($timeline);
    }

    public function store (Request $request)
    {
        $timeline = new Timeline;
        $timeline->title_translation_id = $this->translation_service->store($request->title_translations, 'timeline title');
        $timeline->save();

        $response = [
            "message" => "Timeline created.",
            "timeline" => $timeline
        ];

        return response()->json($response, 201);
    }

    public function update (Request $request, $id)
    {
        $timeline = Timeline::find($id);
        $timeline->save();

        $this->translation_service->update($request->title_translation_id, $request->title_translations);

        $response = [
            "message" => "Timeline updated.",
            "timeline" => $timeline
        ];

        return response()->json($response);
    }

    public function destroy (Request $request, $id)
    {
        $timeline = Timeline::find($id);
        $timeline->delete();

        Translation::destroy($timeline->title_translation_id);

        $response = [
            "message" => "Timeline deleted."
        ];

        return response()->json($response);
    }
}