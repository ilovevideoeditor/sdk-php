<?php

declare(strict_types=1);

namespace iLoveVideoEditor\SDK;

use GuzzleHttp\Client;
use iLoveVideoEditorSDK\Api\RenderApi;
use iLoveVideoEditorSDK\Api\TemplatesApi;
use iLoveVideoEditorSDK\Configuration;
use iLoveVideoEditorSDK\Model\QueueRenderRequest;

/**
 * High-level iLoveVideoEditor client with polling and friendly method names.
 *
 * Usage:
 *   $client = new iLoveVideoEditorClient(['apiKey' => 'vf_live_xxx']);
 *   $result = $client->render(['name' => 'Hello', 'layers' => [...]]);
 *   echo $result->downloadUrl;
 */
class iLoveVideoEditorClient
{
    private RenderApi $renderApi;
    private TemplatesApi $templatesApi;

    public function __construct(array $options)
    {
        $apiKey = $options['apiKey'] ?? '';
        $baseUrl = $options['baseUrl'] ?? 'https://api.ilovevideoeditor.com';

        if (empty($apiKey)) {
            throw new \InvalidArgumentException('apiKey is required');
        }

        $config = Configuration::getDefaultConfiguration()
            ->setHost($baseUrl)
            ->setApiKey('x-api-key', $apiKey);

        $httpClient = new Client();
        $this->renderApi = new RenderApi($httpClient, $config);
        $this->templatesApi = new TemplatesApi($httpClient, $config);
    }

    /**
     * Normalize the API progress payload ({done, total, percent}) to a percent number.
     */
    private static function progressPercent(mixed $progress): float
    {
        if ($progress === null) {
            return 0.0;
        }
        if (is_numeric($progress)) {
            return (float) $progress;
        }
        if (is_object($progress) && method_exists($progress, 'getPercent')) {
            return (float) ($progress->getPercent() ?? 0.0);
        }
        return 0.0;
    }

    /**
     * Submit a VideoJSON payload and block until the render finishes.
     *
     * @param array<string, mixed> $videoJson
     * @param array<string, mixed> $options pollInterval, maxWait, onProgress callable
     */
    public function render(array $videoJson, array $options = []): RenderResult
    {
        $pollInterval = $options['pollInterval'] ?? 2;
        $maxWait = $options['maxWait'] ?? 300;
        $onProgress = $options['onProgress'] ?? null;

        $body = new QueueRenderRequest(['video_json' => $videoJson]);
        $queued = $this->renderApi->queueRender($body);
        $jobId = $queued->getJobId();

        $deadline = time() + $maxWait;
        while (time() < $deadline) {
            $status = $this->renderApi->getRenderStatus($jobId);
            $progress = self::progressPercent($status->getProgress());

            if ($onProgress !== null) {
                $onProgress($status->getStatus(), $progress);
            }

            if ($status->getStatus() === 'completed') {
                $refresh = $this->renderApi->refreshRenderUrl($jobId);
                return new RenderResult(
                    jobId: $jobId,
                    status: $status->getStatus(),
                    progress: $progress,
                    url: $status->getUrl(),
                    downloadUrl: $refresh->getDownloadUrl(),
                    error: $status->getError(),
                    createdAt: $status->getCreatedAt(),
                    completedAt: $status->getCompletedAt(),
                );
            }

            if ($status->getStatus() === 'failed') {
                return new RenderResult(
                    jobId: $jobId,
                    status: $status->getStatus(),
                    progress: $progress,
                    error: $status->getError(),
                    createdAt: $status->getCreatedAt(),
                    completedAt: $status->getCompletedAt(),
                );
            }

            sleep($pollInterval);
        }

        throw new \RuntimeException("Render {$jobId} did not complete within {$maxWait}s");
    }

    public function getRender(string $jobId): RenderResult
    {
        $status = $this->renderApi->getRenderStatus($jobId);
        return new RenderResult(
            jobId: $status->getJobId(),
            status: $status->getStatus(),
            progress: self::progressPercent($status->getProgress()),
            url: $status->getUrl(),
            error: $status->getError(),
            createdAt: $status->getCreatedAt(),
            completedAt: $status->getCompletedAt(),
        );
    }

    public function refreshUrl(string $jobId): string
    {
        $result = $this->renderApi->refreshRenderUrl($jobId);
        return $result->getDownloadUrl();
    }

    /** @return array<int, mixed> */
    public function listTemplates(): array
    {
        return $this->templatesApi->listTemplates()->getTemplates() ?? [];
    }

    public function getTemplate(string $id): mixed
    {
        return $this->templatesApi->getTemplate($id)->getTemplate();
    }
}
