<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Profile;
use App\Models\ProfileCollection;
use App\Models\LanguageTranslation;
use App\Models\Translation;

use App\Services\TranslationService;

class ProfileCollectionController extends Controller
{
    private TranslationService $translation_service;

    public function __construct ()
    {
        $this->translation_service = new TranslationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('profile_collections');
        $query = $this->translation_service->join($query, 'profile_collections', 'title_translation_id', 'translation', $language_code);
        $profile_collections = $query->select('profile_collections.*', 'translation.text AS title')
            ->get();

        return response()->json($profile_collections);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $profile_collection = new ProfileCollection; 
        $profile_collection->title_translation_id = $this->translation_service->store($request->title_translations, 'profile collection title');
        $profile_collection->save();

        $response = [
            "message" => "Profile collection created.",
            "profile_collection" => $profile_collection
        ];

        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $profile_collection = ProfileCollection::find($id);

        $profile_collection->title_translations = $this->translation_service->get('profile_collections', 'title_translation_id', ['id' => $id]);

        return response()->json($profile_collection);
    }

    public function show_profiles(Request $request, string $id) 
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('profile_collections');
        $query = $this->translation_service->join($query, 'profile_collections', 'title_translation_id', 'translation', $language_code);
        $profile_collection = $query->join('profiles', function(JoinClause $join) use ($language_code) {
                $join->on('profile_collections.id', '=', 'profiles.profile_collection_id');
            })
            ->select('profile_collections.*', 'translation.text AS title')
            ->where('profile_collections.id', $id)
            ->first();

        return response()->json($profile_collection);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $profile_collection = ProfileCollection::find($id); 
        $profile_collection->save();
        $this->translation_service->update($request->title_translation_id, $request->title_translations);

        $response = [
            "message" => "Profile collection updated.",
            "profile_collection" => $profile_collection
        ];

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $profile_collection = ProfileCollection::find($id);
        Translation::destroy($profile_collection->title_translation_id);
        $profile_collection->destroy($id);

        $response = [
            "message" => "Profile collection deleted."
        ];

        return response()->json($response);
    }
}
