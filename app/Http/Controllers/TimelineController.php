<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimelineController extends Controller
{
    public function find(Request $request, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $timeline = DB::table('timeline')
            ->find($id);

        $timeline->phases = DB::table('phase')
            ->join('language_translation AS translation', function (JoinClause $join) use ($language_code) {
                $join->on('phase.title_translation_id', '=', 'translation.translation_id')
                    ->where('translation.language_code', '=', $language_code);
            })
            ->select('phase.id', 'phase.timeline_id', 'phase.color', 'translation.text AS title')
            ->where('phase.timeline_id', $id)
            ->get();

        foreach($timeline->phases as $phase){
            $phase->waypoints = DB::table('waypoint')
                ->join('language_translation AS translation', function (JoinClause $join) use ($language_code) {
                    $join->on('waypoint.title_translation_id', '=', 'translation.translation_id')
                        ->where('translation.language_code', '=', $language_code);
                })
                ->select('waypoint.*', 'translation.text AS title')
                ->where('waypoint.phase_id', $phase->id)
                ->get();

            foreach($phase->waypoints as $waypoint){
                if ($waypoint->is_bound) {
                    $waypoint->color = $phase->color;
                }
            }
        }

        return response()->json($timeline);
    }
}
