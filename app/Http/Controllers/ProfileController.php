<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Profile;
use App\Models\ProfileCollection;
use App\Models\LanguageTranslation;
use App\Models\Translation;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = Profile::join('language_translations AS translation', function (JoinClause $join) use ($language_code) {
                $join->on('profiles.title_translation_id', '=', 'translation.translation_id')
                    ->where('translation.language_code', '=', $language_code);
            })
            ->select('profiles.*', 'translation.text AS title');

        if ($request->has('profile_collection_id')) {
            $query = $query->where('profile_collection_id', $request->query('profile_collection_id'));
        }

        $profiles = $query->get();

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
        $translation = new Translation;
        $translation->title = 'profile title';
        $translation->save();

        foreach ($request->title_translations as $title_translation) {
            $language_translation = new LanguageTranslation;
            $language_translation->translation_id = $translation->id;
            $language_translation->language_code = $title_translation['language_code'];
            $language_translation->text = $title_translation['text'];
            $language_translation->save();
        }

        $profile = new Profile; 
        $profile->profile_collection_id = $request->profile_collection_id;
        $profile->article_id = $request->article_id;
        $profile->title_translation_id = $translation->id;
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

        $profile = Profile::join('language_translations AS translation', function (JoinClause $join) use ($language_code) {
                $join->on('profiles.title_translation_id', '=', 'translation.translation_id')
                    ->where('translation.language_code', '=', $language_code);
            })
            ->select('profiles.*', 'translation.text AS title')
            ->where('profiles.id', $id)
            ->first();

        $profile->title_translations = DB::table('profiles')
            ->join('language_translations AS translation', function (JoinClause $join) {
                $join->on('profiles.title_translation_id', '=', 'translation.translation_id');
            })
            ->select('translation.*')
            ->where('profiles.id', $id)
            ->get();

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

        foreach ($request->title_translations as $title_translation) {
            $language_translation = DB::table('language_translations')
                ->where('translation_id', '=', $request->title_translation_id)
                ->where('language_code', '=', $title_translation['language_code'])
                ->update(['text' => $title_translation['text']]);
        }

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
        Profile::destroy($id);

        $response = [
            "message" => "Profile deleted."
        ];

        return response()->json($response);
    }
}
