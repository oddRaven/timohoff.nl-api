<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Article;
use App\Models\LanguageTranslation;
use App\Models\Translation;

use App\Services\TranslationService;

class ArticleController extends Controller
{
    private TranslationService $translation_service;

    public function __construct ()
    {
        $this->translation_service = new TranslationService;
    }

    public function index(Request $request)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $query = DB::table('articles');
        $query = $this->translation_service->join($query, 'articles', 'title_translation_id', 'titleTranslation', $language_code);
        $query = $this->translation_service->join($query, 'articles', 'text_translation_id', 'textTranslation', $language_code);
        $articles = $query->select('articles.id', 'titleTranslation.text AS title', 'textTranslation.text AS text')
            ->get();

        return response()->json($articles);
    }
    
    public function show (Request $request, $id)
    {
        $language_code = $request->header('Content-Language', 'nl');

        $article = Article::find($id);

        $article->title_translations = $this->translation_service->get('articles', 'title_translation_id', ['id' => $id]);
        $article->text_translations = $this->translation_service->get('articles', 'text_translation_id', ['id' => $id]);

        return response()->json($article);
    }

    public function store (Request $request)
    {
        $article = new Article; 
        $article->title_translation_id = $this->translation_service->store($request->title_translations, 'article title');
        $article->text_translation_id = $this->translation_service->store($request->text_translations, 'article text');
        $article->save();

        $response = [
            "message" => "Article created.",
            "article" => $article
        ];

        return response()->json($response, 201);
    }

    public function update (Request $request, $id)
    {
        $article = Article::find($id);
        $article->save();

        $this->translation_service->update($request->title_translation_id, $request->title_translations);
        $this->translation_service->update($request->text_translation_id, $request->text_translations);

        $response = [
            "message" => "Article updated.",
            "article" => $article
        ];

        return response()->json($response);
    }

    public function delete (Request $request, $id)
    {
        $article = Article::find($id);
        $article->delete($id);

        Translation::destroy($article->title_translation_id);
        Translation::destroy($article->text_translation_id);

        $response = [
            "message" => "Article deleted."
        ];

        return response()->json($response);
    }
}
