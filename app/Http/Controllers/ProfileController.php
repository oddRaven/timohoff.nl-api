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

class ProfileController extends Controller
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

        $query = DB::table('profiles');
        $query = $this->translation_service->join($query, 'profiles', 'title_translation_id', 'translation', $language_code);

        if ($request->has('profile_collection_id')) {
            $query = $query->where('profile_collection_id', $request->query('profile_collection_id'));
        }

        $profiles = $query->select('profiles.*', 'translation.text AS title')
            ->get();

        return response()->json($profiles);
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
        $profile = new Profile; 
        $profile->profile_collection_id = $request->profile_collection_id;
        $profile->article_id = $request->article_id;
        $profile->title_translation_id = $this->translation_service->store($request->title_translations, 'profile title');
        $profile->save();

        $response = [
            "message" => "Profile created.",
            "profile" => $profile
        ];

        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('profiles');
        $query = $this->translation_service->join($query, 'profiles', 'title_translation_id', 'translation', $language_code);
        $profile = $query->select('profiles.*', 'translation.text AS title')
            ->where('profiles.id', $id)
            ->first();

        $profile->title_translations = $this->translation_service->get('profiles', 'title_translation_id', ['id' => $id]);

        return response()->json($profile);
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
        $profile = ProfileCollection::find($id); 
        $profile->save();

        $this->translation_service->update($request->title_translation_id, $request->title_translations);

        $response = [
            "message" => "Profile updated.",
            "profile" => $profile
        ];

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $profile = Profile::find($id);
        Translation::destroy($profile->title_translation_id);
        $profile->destroy($id);

        $response = [
            "message" => "Profile deleted."
        ];

        return response()->json($response);
    }
}
