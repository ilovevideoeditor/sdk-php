# iLoveVideoEditor PHP SDK (High-Level Wrapper)

Official high-level PHP SDK for iLoveVideoEditor.

## Installation

```bash
composer require ilovevideoeditor/sdk
```

## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use iLoveVideoEditor\SDK\ILoveVideoEditorClient;

$client = new ILoveVideoEditorClient([
    'apiKey' => 'vf_live_xxx',
]);

$result = $client->render([
    'name' => 'Hello',
    'layers' => [...],
], [
    'onProgress' => fn($status, $progress) => printf("%s — %d%%\n", $status, $progress),
]);

echo $result->downloadUrl;
```
