<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Waypoint;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $timeline = Waypoint::all();

        return response()->json($timeline);
    }

    public function show(Request $request, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $timeline = DB::table('timelines')
            ->find($id);

        if ($request->has('include_phases')) {
            $timeline->phases = DB::table('phases')
                ->join('language_translations AS translation', function (JoinClause $join) use ($language_code) {
                    $join->on('phases.title_translation_id', '=', 'translation.translation_id')
                        ->where('translation.language_code', '=', $language_code);
                })
                ->select('phases.id', 'phases.timeline_id', 'phases.color', 'translation.text AS title')
                ->where('phases.timeline_id', $id)
                ->get();

            if ($request->has('include_waypoints')) {
                foreach($timeline->phases as $phase){
                    $phase->waypoints = DB::table('waypoints')
                        ->join('language_translation AS translation', function (JoinClause $join) use ($language_code) {
                            $join->on('waypoints.title_translation_id', '=', 'translation.translation_id')
                                ->where('translation.language_code', '=', $language_code);
                        })
                        ->select('waypoints.*', 'translation.text AS title')
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
        $timeline->title = $request->title;
        $timeline->save();

        $response = [
            "message" => "Timeline created.",
            "timeline" => $timeline
        ];

        return response()->json($response, 201);
    }

    public function update (Request $request, $id)
    {
        Timeline::find($id)
            ->update(['title' => $request->title]);

        $response = [
            "message" => "Timeline updated.",
            "timeline" => $timeline
        ];

        $timeline = Timeline::find($id);

        return response()->json($response);
    }

    public function destroy (Request $request, $id)
    {
        Timeline::destroy($id);

        $response = [
            "message" => "Timeline deleted."
        ];

        return response()->json($response);
    }
}