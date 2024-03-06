<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionItem extends Model
{
    use HasFactory;
    protected $table = 'section_items';
    protected $fillable = ['item_id', 'item_type', 'section_id', 'title_translation_id', 'order'];
}
