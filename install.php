<?php
/**
 * Golden Bird Charcoal CMS - Root Installer Entry Point
 * Redirects or includes public/install.php for cPanel & shared hosts
 */

if (file_exists(__DIR__ . '/public/install.php')) {
    require_once __DIR__ . '/public/install.php';
    exit;
}

header('Location: public/install.php');
exit;
