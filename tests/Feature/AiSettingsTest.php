<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AppSetting;
use App\Models\User;
use App\Support\AiSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_ai_api_settings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('AI Teaching Copilot');

        $this->actingAs($admin)
            ->patch(route('settings.ai'), [
                'enabled' => '1',
                'driver' => 'openai',
                'api_key' => 'sk-test-secret-key-123456',
                'base_url' => 'https://api.openai.com/v1',
                'model' => 'gpt-4o-mini',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('app_settings', ['key' => 'ai.driver']);
        $this->assertNotSame('sk-test-secret-key-123456', AppSetting::query()->where('key', 'ai.api_key')->value('value'));

        AiSettings::applyToConfig();
        $this->assertSame('openai', config('ai.driver'));
        $this->assertSame('sk-test-secret-key-123456', config('ai.api_key'));
    }

    public function test_lecturer_cannot_update_ai_settings(): void
    {
        $lecturer = User::factory()->create(['role' => UserRole::Lecturer]);

        $this->actingAs($lecturer)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertDontSee('AI Teaching Copilot');

        $this->actingAs($lecturer)
            ->patch(route('settings.ai'), [
                'enabled' => '1',
                'driver' => 'fake',
                'base_url' => 'https://api.openai.com/v1',
                'model' => 'gpt-4o-mini',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_clear_api_key(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        AiSettings::save([
            'enabled' => true,
            'driver' => 'openai',
            'api_key' => 'sk-keep-me',
            'base_url' => 'https://api.openai.com/v1',
            'model' => 'gpt-4o-mini',
        ]);

        $this->actingAs($admin)
            ->patch(route('settings.ai'), [
                'enabled' => '1',
                'driver' => 'fake',
                'clear_api_key' => '1',
                'base_url' => 'https://api.openai.com/v1',
                'model' => 'gpt-4o-mini',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('app_settings', ['key' => 'ai.api_key']);
    }
}
