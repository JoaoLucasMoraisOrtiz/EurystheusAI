<?php

namespace Tests\Feature;

use App\Models\PromptLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_prompt_with_no_parent()
    {
        $prompt = PromptLog::create([
            'anonymous_user' => 999,
            'content' => 'Meu primeiro prompt',
        ]);

        $this->assertNotNull($prompt->id);
        $this->assertNull($prompt->parent_id);
    }

    public function test_can_link_prompts_in_chain()
    {
        $parentPrompt = PromptLog::create([
            'anonymous_user' => 999,
            'content' => 'Prompt pai',
        ]);

        $childPrompt = PromptLog::create([
            'anonymous_user' => 999,
            'parent_id' => $parentPrompt->id,
            'content' => 'Prompt filho',
        ]);

        $this->assertEquals($parentPrompt->id, $childPrompt->parent->id);
        $this->assertCount(1, $parentPrompt->children);
    }
}
