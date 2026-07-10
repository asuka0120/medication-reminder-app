<?php

define('LARAVEL_START', microtime(true));

// ストレージパスを /tmp に向ける
$_ENV['APP_STORAGE'] = '/tmp';

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// キャッシュパスを /tmp に設定
$app->useStoragePath('/tmp/storage');

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
