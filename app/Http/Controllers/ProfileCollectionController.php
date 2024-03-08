<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Profile;
use App\Models\ProfileCollection;
use App\Models\LanguageTranslation;
use App\Models\Translation;

class ProfileCollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $profile_collections = ProfileCollection::join('language_translations AS translation', function (JoinClause $join) use ($language_code) {
                $join->on('profile_collections.title_translation_id', '=', 'translation.translation_id')
                    ->where('translation.language_code', '=', $language_code);
            })
            ->select('profile_collections.*', 'translation.text AS title')
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
        $translation = new Translation;
        $translation->title = 'profile collection title';
        $translation->save();

        foreach ($request->title_translations as $title_translation) {
            $language_translation = new LanguageTranslation;
            $language_translation->translation_id = $translation->id;
            $language_translation->language_code = $title_translation['language_code'];
            $language_translation->text = $title_translation['text'];
            $language_translation->save();
        }

        $profile_collection = new ProfileCollection; 
        $profile_collection->title_translation_id = $translation->id;
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

        $profile_collection = ProfileCollection::join('language_translations AS translation', function (JoinClause $join) use ($language_code) {
                $join->on('profile_collections.title_translation_id', '=', 'translation.translation_id')
                    ->where('translation.language_code', '=', $language_code);
            })
            ->select('profile_collections.*', 'translation.text AS title')
            ->where('profile_collections.id', $id)
            ->first();

        return response()->json($profile_collection);
    }

    public function show_profiles(Request $request, string $id) 
    {
        $language_code = $request->header('Content-Language', 'nl');

        $profile_collection = ProfileCollection::join('language_translations AS translation', function (JoinClause $join) use ($language_code) {
                $join->on('profile_collections.title_translation_id', '=', 'translation.translation_id')
                    ->where('translation.language_code', '=', $language_code);
            })
            ->join('profiles', function(JoinClause $join) use ($language_code) {
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

        foreach ($request->title_translations as $title_translation) {
            $language_translation = DB::table('language_translations')
                ->where('translation_id', '=', $request->title_translation_id)
                ->where('language_code', '=', $title_translation['language_code'])
                ->update(['text' => $title_translation['text']]);
        }

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
        ProfileCollection::destroy($id);

        $response = [
            "message" => "Profile collection deleted."
        ];

        return response()->json($response);
    }
}
