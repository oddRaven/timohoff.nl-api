<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;

use App\Models\Waypoint;
use App\Models\Translation;
use App\Services\TranslationService;

class WaypointController extends Controller
{
    private TranslationService $translation_service;

    public function __construct ()
    {
        $this->translation_service = new TranslationService;
    }

    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('waypoints');
        $query = $this->translation_service->join($query, 'waypoints', 'title_translation_id', 'translation', $language_code);

        if ($request->has('phase_id')) {
            $query = $query->where('waypoints.phase_id', $request->query('phase_id'));
        }

        $waypoints = $query->select('waypoints.id', 'waypoints.phase_id', 'waypoints.article_id', 'waypoints.location', 'waypoints.image_name', 'waypoints.is_bound', 'translation.text AS title')
            ->get();

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

        if ($request->has('include_language_translations')) {
            $waypoint->title_translations = $this->translation_service->get('waypoints', 'title_translation_id', ['id' => $id]);
        }

        return response()->json($waypoint);
    }

    public function store (Request $request)
    {
        $title_translation_id = $this->translation_service->store($request->title_translations, 'waypoint title');

        $waypoint = new Waypoint;
        $waypoint->phase_id = $request->phase_id;
        $waypoint->article_id = $request->article_id;
        $waypoint->title_translation_id = $title_translation_id;
        $waypoint->image_name = $request->image_name;
        $waypoint->is_bound = $request->is_bound;
        $waypoint->location = $request->location;
        $waypoint->save();

        $response = [
            "message" => "Waypoint created.",
            "waypoint" => $waypoint
        ];

        return response()->json($response, 201);
    }

    public function update (Request $request, $id)
    {
        $waypoint = Waypoint::find($id);

        DB::table('waypoints')
            ->where('id', $id)
            ->update([
                'phase_id' => $request->phase_id,
                'article_id' => $request->article_id,
                'image_name' => $request->image_name,
                'is_bound' => $request->is_bound,
                'location' => $request->location,
                'updated_at' => now()
            ]);

        if ($waypoint !== null) {
            $this->translation_service->update($waypoint->title_translation_id, $request->title_translations);
        }

        $updated_waypoint = Waypoint::find($id);

        $response = [
            "message" => "Waypoint updated.",
            "waypoint" => $updated_waypoint
        ];

        return response()->json($response);
    }

    public function destroy (Request $request, $id)
    {
        $waypoint = Waypoint::find($id);
        $waypoint->delete();

        Translation::destroy($waypoint->title_translation_id);

        $response = [
            "message" => "Waypoint deleted."
        ];

        return response()->json($response);
    }
}
