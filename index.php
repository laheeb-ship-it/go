<?php
/**
 * Golden Bird Charcoal CMS - Root Front Controller & Safe Proxy
 * Compatible with cPanel where document root is set to project root
 */

if (file_exists(__DIR__ . '/public/index.php')) {
    require_once __DIR__ . '/public/index.php';
    exit;
}

// Fallback direct boot
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/routes/web.php';

$request = new \App\Core\Request();
$response = new \App\Core\Response();
$router = new \App\Core\Router($request, $response);
registerRoutes($router);
$router->dispatch();
