<?php

namespace Tests\Feature;

// 1. Update this namespace to match your actual service:
use App\Integrations\Turbo\Services\TurboService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TurboApiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Make sure config keys match what TurboService constructor expects:
        config([
            'turbo.base_url' => 'https://platform.turbo.info',
            'turbo.auth_key' => env('TURBO_AUTHENTICATION_KEY', 'test_key'),
            'turbo.client_code' => env('TURBO_MAIN_CLIENT_CODE', '25847'),
        ]);
    }

    public function test_get_governments_returns_data()
    {
        // 2. Instantiate TurboService instead of TurboApiService
        $turboService = new TurboService;

        fwrite(STDERR, "\n--- Testing Get Governments ---\n");

        try {
            $response = $turboService->getGovernments();

            $this->assertIsArray($response);
            fwrite(STDERR, "   ✓ Governments fetched successfully.\n");
            fwrite(STDERR, json_encode(array_slice($response, 0, 3), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");

        } catch (RuntimeException $e) {
            fwrite(STDERR, "   ⚠️ Get Governments API error: {$e->getMessage()}\n");
            $this->fail("Failed to get governments: {$e->getMessage()}");
        }
    }

    public function test_get_areas_returns_data_for_government()
    {
        $turboService = new TurboService;
        $testGovernment = 'القاهرة';

        fwrite(STDERR, "\n--- Testing Get Areas for '{$testGovernment}' ---\n");

        try {
            $response = $turboService->getAreas(1);

            $this->assertIsArray($response);
            fwrite(STDERR, "   ✓ Areas fetched successfully for '{$testGovernment}'.\n");
            fwrite(STDERR, json_encode(array_slice($response, 0, 3), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");

        } catch (RuntimeException $e) {
            fwrite(STDERR, "   ⚠️ Get Areas API error: {$e->getMessage()}\n");
            $this->fail("Failed to get areas: {$e->getMessage()}");
        }
    }
}
