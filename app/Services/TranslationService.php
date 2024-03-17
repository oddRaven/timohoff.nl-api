<?php

namespace App\Services;

use App\Models\LanguageTranslation;
use App\Models\Translation;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class TranslationService
{
    public function get($table, $column, $identifiers)
    {
        return DB::table($table)
            ->join('language_translations AS translation', function (JoinClause $join) use ($table, $column) {
                $join->on("{$table}.{$column}", '=', 'translation.translation_id');
            })
            ->select('translation.*')
            ->where($identifiers)
            ->get();
    }

    public function join ($query, $table, $column, $alias, $language_code){
        return $query->join("language_translations AS {$alias}", function (JoinClause $join) use ($table, $column, $alias, $language_code) {
            $join->on("{$table}.{$column}", '=', "{$alias}.translation_id")
                ->where("{$alias}.language_code", '=', $language_code);
        });
    }

    public function store ($subject_translations, $translation_title) 
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

    public function update ($translation_id, $translations)
    {
        foreach ($translations as $translation) {
            $language_translation = DB::table('language_translations')
                ->where('translation_id', '=', $translation_id)
                ->where('language_code', '=', $translation['language_code'])
                ->update(['text' => $translation['text']]);
        }
    }
}