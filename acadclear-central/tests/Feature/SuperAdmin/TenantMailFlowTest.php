<?php

namespace Tests\Feature\SuperAdmin;

use App\Mail\UniversityCredentialsMail;
use App\Models\Plan;
use App\Models\User;
use App\Services\TenantDatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class TenantMailFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_creation_reports_logged_preview_when_mailer_is_log(): void
    {
        config(['mail.default' => 'log']);
        Mail::fake();

        $this->mockTenantDatabaseManager();

        $response = $this->actingAs(User::factory()->create())
            ->post(route('super-admin.tenants.store'), $this->tenantPayload());

        $response->assertRedirect(route('super-admin.tenants.index'));
        $response->assertSessionHas('success', function (string $message): bool {
            return str_contains($message, 'MAIL_MAILER is set to log')
                && str_contains($message, 'storage/logs/laravel.log');
        });

        Mail::assertSent(UniversityCredentialsMail::class, function (UniversityCredentialsMail $mail): bool {
            return $mail->hasTo('admin@sample.edu');
        });
    }

    public function test_tenant_creation_reports_real_delivery_when_mailer_is_smtp(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();

        $this->mockTenantDatabaseManager();

        $response = $this->actingAs(User::factory()->create())
            ->post(route('super-admin.tenants.store'), $this->tenantPayload([
                'slug' => 'smtp-university',
                'domain' => 'smtp.acadclear.com',
                'database' => 'acadclear_smtp_university',
                'admin_email' => 'smtp-admin@sample.edu',
            ]));

        $response->assertRedirect(route('super-admin.tenants.index'));
        $response->assertSessionHas('success', function (string $message): bool {
            return str_contains($message, 'Credentials email sent to smtp-admin@sample.edu.')
                && ! str_contains($message, 'not delivered to an inbox');
        });

        Mail::assertSent(UniversityCredentialsMail::class, function (UniversityCredentialsMail $mail): bool {
            return $mail->hasTo('smtp-admin@sample.edu');
        });
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function tenantPayload(array $overrides = []): array
    {
        /** @var Plan $plan */
        $plan = Plan::query()->firstOrFail();

        return array_merge([
            'name' => 'Sample University',
            'slug' => 'sample-university',
            'domain' => 'sample.acadclear.com',
            'database' => 'acadclear_sample_university',
            'plan_id' => $plan->id,
            'admin_email' => 'admin@sample.edu',
            'admin_password' => 'password123',
        ], $overrides);
    }

    private function mockTenantDatabaseManager(): void
    {
        $mock = Mockery::mock(TenantDatabaseManager::class);
        $mock->shouldReceive('createTenant')
            ->once()
            ->andReturn(true);

        $this->app->instance(TenantDatabaseManager::class, $mock);
    }
}
