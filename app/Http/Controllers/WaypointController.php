<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;

use App\Models\Waypoint;

class WaypointController extends Controller
{
    public function index() 
    {
        $waypoints = Waypoint::all();

        return response()->json($waypoints);
    }

    public function find(Request $request, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $waypoint = DB::table('waypoint')
            ->join('language_translation AS translation', function (JoinClause $join) use ($language_code) {
                $join->on('waypoint.title_translation_id', '=', 'translation.translation_id')
                    ->where('translation.language_code', '=', $language_code);
            })
            ->select('waypoint.*', 'translation.text AS title')
            ->where('waypoint.id', $id)
            ->first();

        $waypoint->article = DB::table('article')
            ->join('language_translation AS titleTranslation', function (JoinClause $join) use ($language_code) {
                $join->on('article.title_translation_id', '=', 'titleTranslation.translation_id')
                    ->where('titleTranslation.language_code', '=', $language_code);
            })
            ->join('language_translation AS textTranslation', function (JoinClause $join) use ($language_code) {
                $join->on('article.text_translation_id', '=', 'textTranslation.translation_id')
                    ->where('textTranslation.language_code', '=', $language_code);
            })
            ->select('article.id', 'titleTranslation.text AS title', 'textTranslation.text AS text')
            ->where('article.id', $waypoint->article_id)
            ->first();

        return response()->json($waypoint);
    }
}
