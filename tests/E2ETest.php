<?php

declare(strict_types=1);

/**
 * End-to-end tests for the PHP SDK against a local Prism mock server.
 *
 * Run with:
 *   vendor/bin/phpunit tests/E2ETest.php
 *
 * Environment:
 *   SDK_TEST_BASE_URL     (default http://127.0.0.1:4010)
 *   SDK_TEST_API_KEY      (default test-key)
 *   SDK_TEST_BEARER_TOKEN (default test-token)
 */

namespace iLoveVideoEditor\SDK\Test;

use GuzzleHttp\Client;
use iLoveVideoEditor\SDK\iLoveVideoEditorClient;
use iLoveVideoEditorSDK\Api\HealthApi;
use iLoveVideoEditorSDK\Api\ProjectsApi;
use iLoveVideoEditorSDK\Api\RenderApi;
use iLoveVideoEditorSDK\Api\TemplatesApi;
use iLoveVideoEditorSDK\Configuration;
use iLoveVideoEditorSDK\Model\EstimateRenderCostRequest;
use iLoveVideoEditorSDK\Model\QueueRenderRequest;
use PHPUnit\Framework\TestCase;

final class E2ETest extends TestCase
{
    private static function envOr(string $key, string $fallback): string
    {
        $value = getenv($key);
        return $value !== false && $value !== '' ? $value : $fallback;
    }

    private static function config(): Configuration
    {
        return Configuration::getDefaultConfiguration()
            ->setHost(self::envOr('SDK_TEST_BASE_URL', 'http://127.0.0.1:4010'))
            ->setApiKey('x-api-key', self::envOr('SDK_TEST_API_KEY', 'test-key'))
            ->setAccessToken(self::envOr('SDK_TEST_BEARER_TOKEN', 'test-token'));
    }

    /**
     * @return array<string, mixed>
     */
    private static function videoJson(): array
    {
        return [
            'name' => 'e2e-test',
            'layers' => [
                ['type' => 'composition', 'width' => 1920, 'height' => 1080, 'fps' => 30],
            ],
        ];
    }

    public function testHealthCheck(): void
    {
        $api = new HealthApi(new Client(), self::config());
        $status = $api->healthCheck();
        $this->assertNotEmpty($status->getStatus());
    }

    public function testListTemplatesGenerated(): void
    {
        $api = new TemplatesApi(new Client(), self::config());
        $result = $api->listTemplates();
        $this->assertIsArray($result->getTemplates());
    }

    public function testListTemplatesWrapper(): void
    {
        $client = new iLoveVideoEditorClient([
            'apiKey' => self::envOr('SDK_TEST_API_KEY', 'test-key'),
            'baseUrl' => self::envOr('SDK_TEST_BASE_URL', 'http://127.0.0.1:4010'),
        ]);
        $this->assertIsArray($client->listTemplates());
    }

    public function testQueueRender(): void
    {
        $api = new RenderApi(new Client(), self::config());
        $body = new QueueRenderRequest(['video_json' => self::videoJson()]);
        $result = $api->queueRender($body);
        $this->assertNotEmpty($result->getJobId());
        $this->assertNotEmpty($result->getStatus());
    }

    public function testEstimateRenderCost(): void
    {
        $api = new RenderApi(new Client(), self::config());
        $body = new EstimateRenderCostRequest(['video_json' => self::videoJson()]);
        $estimate = $api->estimateRenderCost($body);
        $this->assertGreaterThan(0, $estimate->getCost());
        $this->assertGreaterThan(0, $estimate->getEstimatedDuration());
    }

    public function testListProjects(): void
    {
        $api = new ProjectsApi(new Client(), self::config());
        $result = $api->listProjects(1, 10);
        $this->assertIsArray($result->getProjects());
    }
}
