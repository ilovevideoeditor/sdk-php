# iLoveVideoEditor PHP SDK

Official PHP SDK for iLoveVideoEditor — render videos programmatically with a cloud video API.

iLoveVideoEditor is a cloud video rendering API: submit a JSON scene description or template, queue a render, and download the resulting MP4/WebM. This package is the official PHP client, combining a friendly high-level wrapper (queue + poll + download URL in one call) with a fully generated API client covering every endpoint.

[![Packagist Version](https://img.shields.io/packagist/v/ilovevideoeditor/sdk.svg)](https://packagist.org/packages/ilovevideoeditor/sdk) [![PHP Version](https://img.shields.io/packagist/php-v/ilovevideoeditor/sdk.svg)](https://packagist.org/packages/ilovevideoeditor/sdk) [![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE) [![Docs](https://img.shields.io/badge/docs-ilovevideoeditor.com-blue)](https://ilovevideoeditor.com/docs/sdks)

## Features

- **One-call rendering** — submit a VideoJSON scene description with `render()` and block until the MP4/WebM is ready
- **Progress callbacks** — `onProgress($status, $percent)` plus configurable `pollInterval` and `maxWait`
- **Render management** — check job status with `getRender()` and refresh expiring download URLs with `refreshUrl()`
- **Templates** — list and fetch templates with `listTemplates()` / `getTemplate()`, or render them via the generated `TemplatesApi::renderTemplate()`
- **Full API coverage** — a generated client (`iLoveVideoEditorSDK\Api\*`) for renders, templates, assets, projects, webhooks, workflows, API keys, billing, tools, renditions, integrations, and health — including render cancellation and cost estimation
- **PHP 8.1+** with strict types, readonly result objects, and Guzzle 7 under the hood

## Installation

```bash
composer require ilovevideoeditor/sdk
```

Requires **PHP >= 8.1** with the `curl`, `json`, and `mbstring` extensions.

## Quick start

```php
<?php
require_once 'vendor/autoload.php';

use iLoveVideoEditor\SDK\iLoveVideoEditorClient;

$client = new iLoveVideoEditorClient([
    'apiKey' => getenv('ILOVEVIDEOEDITOR_API_KEY'),
]);

$result = $client->render([
    'name' => 'hello-world',
    'layers' => [
        ['type' => 'composition', 'width' => 1920, 'height' => 1080, 'fps' => 30],
    ],
], [
    'onProgress' => fn(string $status, float $progress) => printf("%s — %.0f%%\n", $status, $progress),
]);

echo $result->downloadUrl, PHP_EOL;
```

`render()` queues the job, polls until it completes, and returns a `RenderResult` with `jobId`, `status`, `progress`, `url`, `downloadUrl`, `error`, `createdAt`, and `completedAt`.

## Authentication

Create an API key in your iLoveVideoEditor dashboard. Keys are prefixed with `vf_live_` and are sent as the `x-api-key` header. Keep the key out of source control — read it from an environment variable:

```php
$client = new iLoveVideoEditorClient([
    'apiKey' => getenv('ILOVEVIDEOEDITOR_API_KEY'),
]);
```

## Documentation

- Docs: https://ilovevideoeditor.com/docs
- SDK guides: https://ilovevideoeditor.com/docs/sdks
- Packagist: https://packagist.org/packages/ilovevideoeditor/sdk

## Other official SDKs

- **Node.js / TypeScript**: https://www.npmjs.com/package/@ilovevideoeditor/sdk-node (repo: https://github.com/ilovevideoeditor/sdk-node)
- **Python**: https://pypi.org/project/ilovevideoeditor-sdk/ (repo: https://github.com/ilovevideoeditor/sdk-python)
- **Ruby**: https://rubygems.org/gems/ilovevideoeditor-sdk (repo: https://github.com/ilovevideoeditor/sdk-ruby)
- **Go**: https://pkg.go.dev/github.com/ilovevideoeditor/sdk-go (repo: https://github.com/ilovevideoeditor/sdk-go)

## License

MIT — see [LICENSE](LICENSE).
