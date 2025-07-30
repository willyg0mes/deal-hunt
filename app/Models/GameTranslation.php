<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'field_name',
        'original_text',
        'translated_text',
        'translation_method',
        'confidence_score',
        'translated_at'
    ];

    protected $casts = [
        'translated_at' => 'datetime',
        'confidence_score' => 'float'
    ];

    // Relacionamento com Game
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
