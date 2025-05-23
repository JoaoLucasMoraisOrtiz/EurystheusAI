<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LlmResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt_log_id',
        'llm_reasoning',
        'generated_prompts',
    ];

    public function promptLog()
    {
        return $this->belongsTo(PromptLog::class);
    }
}
