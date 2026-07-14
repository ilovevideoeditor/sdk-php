<?php

declare(strict_types=1);

namespace iLoveVideoEditor\SDK;

/**
 * Convenience container for a render job result.
 */
class RenderResult
{
    public function __construct(
        public readonly string $jobId,
        public readonly string $status,
        public readonly float $progress = 0.0,
        public readonly ?string $url = null,
        public readonly ?string $downloadUrl = null,
        public readonly ?string $error = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $completedAt = null,
    ) {
    }
}
