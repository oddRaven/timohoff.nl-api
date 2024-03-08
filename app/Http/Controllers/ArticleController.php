<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Article;
use App\Models\LanguageTranslation;
use App\Models\Translation;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $articles = DB::table('articles')
            ->join('language_translations AS titleTranslation', function (JoinClause $join) use ($language_code) {
                $join->on('articles.title_translation_id', '=', 'titleTranslation.translation_id')
                    ->where('titleTranslation.language_code', '=', $language_code);
            })
            ->join('language_translations AS textTranslation', function (JoinClause $join) use ($language_code) {
                $join->on('articles.text_translation_id', '=', 'textTranslation.translation_id')
                    ->where('textTranslation.language_code', '=', $language_code);
            })
            ->select('articles.id', 'titleTranslation.text AS title', 'textTranslation.text AS text')
            ->get();

        return response()->json($articles);
    }
    
    public function find (Request $request, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $article = Article::find($id);

        $article->title_translations = DB::table('articles')
            ->join('language_translations AS translation', function (JoinClause $join) {
                $join->on('articles.title_translation_id', '=', 'translation.translation_id');
            })
            ->select('translation.language_code AS code', 'translation.text AS text')
            ->where('articles.id', $id)
            ->get();

        $article->text_translations = DB::table('articles')
            ->join('language_translations AS translation', function (JoinClause $join) {
                $join->on('articles.text_translation_id', '=', 'translation.translation_id');
            })
            ->select('translation.language_code AS code', 'translation.text AS text')
            ->where('articles.id', $id)
            ->get();

        return response()->json($article);
    }

    public function store (Request $request)
    {
        $article = new Article; 
        $article->title_translation_id = $this->store_translation($request->title_translations, 'article title');
        $article->text_translation_id = $this->store_translation($request->text_translations, 'article text');
        $article->save();

        $response = [
            "message" => "Article created.",
            "article" => $article
        ];

        return response()->json($response, 201);
    }

    private function store_translation ($subject_translations, $translation_title) 
    {
        $translation = new Translation;
        $translation->title = $translation_title;
        $translation->save();

        foreach ($subject_translations as $subject_translation) {
            $language_translation = new LanguageTranslation;
            $language_translation->translation_id = $translation->id;
            $language_translation->language_code = $subject_translation['language_code'];
            $language_translation->text = $subject_translation['text'];
            $language_translation->save();
        }

        return $translation->id;
    }

    public function update (Request $request, $id)
    {
        $article = Article::find($id); 
        $article->save();

        $this->update_translations($request->title_translation_id, $request->title_translations);
        $this->update_translations($request->text_translation_id, $request->text_translations);

        $response = [
            "message" => "Article updated.",
            "article" => $article
        ];

        return response()->json($response);
    }

    private function update_translations($translation_id, $translations)
    {
        foreach ($request->translations as $translation) {
            $language_translation = DB::table('language_translations')
                ->where('translation_id', '=', $translation_id)
                ->where('language_code', '=', $translation['language_code'])
                ->update(['text' => $translation['text']]);
        }
    }

    public function delete (Request $request, $id)
    {
        Article::destroy($id); 

        $response = [
            "message" => "Article deleted."
        ];

        return response()->json($response);
    }
}
