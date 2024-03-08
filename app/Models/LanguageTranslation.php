<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageTranslation extends Model
{
    use HasFactory;
    protected $table = 'language_translations';
    protected $fillable = ['translation_id', 'language_code', 'text'];
}