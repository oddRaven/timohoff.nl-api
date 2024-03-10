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

    public function show(Request $request, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $waypoint = DB::table('waypoints')
            ->join('language_translations AS translation', function (JoinClause $join) use ($language_code) {
                $join->on('waypoints.title_translation_id', '=', 'translation.translation_id')
                    ->where('translation.language_code', '=', $language_code);
            })
            ->select('waypoints.*', 'translation.text AS title')
            ->where('waypoints.id', $id)
            ->first();

        $waypoint->article = DB::table('articles')
            ->join('language_translations AS titleTranslation', function (JoinClause $join) use ($language_code) {
                $join->on('articles.title_translation_id', '=', 'titleTranslation.translation_id')
                    ->where('titleTranslation.language_code', '=', $language_code);
            })
            ->join('language_translations AS textTranslation', function (JoinClause $join) use ($language_code) {
                $join->on('articles.text_translation_id', '=', 'textTranslation.translation_id')
                    ->where('textTranslation.language_code', '=', $language_code);
            })
            ->select('articles.id', 'titleTranslation.text AS title', 'textTranslation.text AS text')
            ->where('articles.id', $waypoint->article_id)
            ->first();

        return response()->json($waypoint);
    }

    public function store (Request $request)
    {
        $waypoint = new Waypoint;
        $waypoint->title = $request->title;
        $waypoint->image_source = $request->image_source;
        $waypoint->is_bound = $request->is_bound;
        $waypoint->save();

        $response = [
            "message" => "Waypoint created.",
            "timeline" => $waypoint
        ];

        return response()->json($response, 201);
    }

    public function update (Request $request, $id)
    {
        Waypoint::find($id)
            ->update(['title' => $request->title, 'image_source' => $request->image_source, 'is_bound' => $request->is_bound]);

        $waypoint = Waypoint::find($id);

        $response = [
            "message" => "Waypoint updated.",
            "waypoint" => $waypoint
        ];

        return response()->json($response);
    }

    public function destroy (Request $request, $id)
    {
        Waypoint::destroy($id);

        $response = [
            "message" => "Waypoint deleted."
        ];

        return response()->json($response);
    }
}
