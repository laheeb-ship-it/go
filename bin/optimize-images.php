<?php
/**
 * CLI Tool: Batch Image Optimizer for Golden Bird Charcoal
 * Usage: php bin/optimize-images.php [--force]
 */

require_once __DIR__ . '/../app/Config/app.php';
require_once __DIR__ . '/../app/Helpers/ImageOptimizer.php';

use App\Helpers\ImageOptimizer;

echo "========================================================\n";
echo "  Golden Bird Charcoal - Batch Modern Image Optimizer\n";
echo "  Targeting WebP, AVIF, 600w, 900w Responsive Formats\n";
echo "========================================================\n\n";

$force = in_array('--force', $argv, true);
$root = dirname(__DIR__);

$uploadsDir = $root . '/public/uploads';
$assetsDir = $root . '/public/assets/images';

echo "1. Scanning & Optimizing uploads in: {$uploadsDir}\n";
$upRes = ImageOptimizer::optimizeDirectory($uploadsDir, $force);
echo "   Processed: {$upRes['processed_images']} images, Generated: {$upRes['generated_variants']} modern variants.\n\n";

echo "2. Scanning & Optimizing assets in: {$assetsDir}\n";
$asRes = ImageOptimizer::optimizeDirectory($assetsDir, $force);
echo "   Processed: {$asRes['processed_images']} images, Generated: {$asRes['generated_variants']} modern variants.\n\n";

echo "✓ Batch Optimization Finished Successfully.\n";
