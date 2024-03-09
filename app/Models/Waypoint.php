<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waypoint extends Model
{
    use HasFactory;
    protected $table = 'waypoints';
    protected $fillable = ['phase_id', 'article_id', 'title_translation_id', 'title', 'image_source', 'is_bound'];
}
