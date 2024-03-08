<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileCollection extends Model
{
    use HasFactory;
    protected $table = 'profile_collections';
    protected $fillable = ['title_translation_id'];
}
