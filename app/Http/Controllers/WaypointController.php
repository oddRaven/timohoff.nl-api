<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;

use App\Models\Waypoint;
use App\Services\TranslationService;

class WaypointController extends Controller
{
    private TranslationService $translation_service;

    public function __construct ()
    {
        $this->translation_service = new TranslationService;
    }

    public function index() 
    {
        $waypoints = Waypoint::all();

        return response()->json($waypoints);
    }

    public function show(Request $request, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('waypoints');
        $query = $this->translation_service->join($query, 'waypoints', 'title_translation_id', 'translation', $language_code);
        $waypoint = $query->select('waypoints.*', 'translation.text AS title')
            ->where('waypoints.id', $id)
            ->first();

        $query = DB::table('articles');
        $query = $this->translation_service->join($query, 'articles', 'title_translation_id', 'titleTranslation', $language_code);
        $query = $this->translation_service->join($query, 'articles', 'title_translation_id', 'textTranslation', $language_code);
        $waypoint->article = $query->select('articles.id', 'titleTranslation.text AS title', 'textTranslation.text AS text')
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
        $waypoint = Waypoint::find($id);
        Waypoint::destroy($waypoint->title_translation_id);
        $waypoint->delete();

        $response = [
            "message" => "Waypoint deleted."
        ];

        return response()->json($response);
    }
}
