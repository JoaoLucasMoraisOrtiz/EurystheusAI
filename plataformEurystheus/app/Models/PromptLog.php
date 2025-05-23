<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'anonymous_user',
        'parent_id',
        'content',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function llmResponse()
    {
        return $this->hasOne(LlmResponse::class);
    }
}
