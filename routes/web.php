<?php
/**
 * Golden Bird Charcoal CMS - Web Routes Definition
 */

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\ThrottleMiddleware;

/**
 * Register all application routes on the router
 */
if (!function_exists('registerRoutes')) {
function registerRoutes(Router $router): void
{
    // ==========================================
    // 1. Frontend Public Routes
    // ==========================================
    $router->get('/install', fn($req, $res) => $res->redirect('/install.php'));
    $router->get('/installer', fn($req, $res) => $res->redirect('/install.php'));
    $router->get('/', [\App\Controllers\Frontend\HomeController::class, 'index']);
    $router->get('/about', fn($req, $res) => (new \App\Controllers\Frontend\PageController($req, $res))->show('about'));
    $router->get('/about-us', fn($req, $res) => (new \App\Controllers\Frontend\PageController($req, $res))->show('about'));
    $router->get('/quality-certificates', fn($req, $res) => (new \App\Controllers\Frontend\PageController($req, $res))->show('quality-certifications'));
    $router->get('/certificates', fn($req, $res) => (new \App\Controllers\Frontend\PageController($req, $res))->show('quality-certifications'));
    $router->get('/quality', fn($req, $res) => (new \App\Controllers\Frontend\PageController($req, $res))->show('quality-certifications'));
    $router->get('/export-markets', fn($req, $res) => (new \App\Controllers\Frontend\PageController($req, $res))->show('export-markets'));
    $router->get('/markets', fn($req, $res) => (new \App\Controllers\Frontend\PageController($req, $res))->show('export-markets'));
    $router->get('/packaging-options', fn($req, $res) => (new \App\Controllers\Frontend\SectionController($req, $res))->show('packaging-private-label'));
    $router->get('/private-label', fn($req, $res) => (new \App\Controllers\Frontend\SectionController($req, $res))->show('packaging-private-label'));
    $router->get('/export-sections', [\App\Controllers\Frontend\SectionController::class, 'index']);
    $router->get('/export/{slug}', [\App\Controllers\Frontend\SectionController::class, 'show']);
    $router->get('/products', [\App\Controllers\Frontend\ProductController::class, 'index']);
    $router->get('/product/{slug}', [\App\Controllers\Frontend\ProductController::class, 'show']);
    $router->get('/page/{slug}', [\App\Controllers\Frontend\PageController::class, 'show']);
    $router->get('/blog', [\App\Controllers\Frontend\BlogController::class, 'index']);
    $router->get('/blog/{slug}', [\App\Controllers\Frontend\BlogController::class, 'show']);
    $router->get('/request-quote', [\App\Controllers\Frontend\QuoteController::class, 'create']);
    $router->post('/request-quote/submit', [\App\Controllers\Frontend\QuoteController::class, 'store'], [ThrottleMiddleware::class]);
    $router->get('/contact', [\App\Controllers\Frontend\ContactController::class, 'index']);
    $router->post('/contact/send', [\App\Controllers\Frontend\ContactController::class, 'send'], [ThrottleMiddleware::class]);
    $router->post('/contact/submit', [\App\Controllers\Frontend\ContactController::class, 'send'], [ThrottleMiddleware::class]);
    $router->post('/api/web-vitals', [\App\Controllers\Frontend\WebVitalsController::class, 'store'], [ThrottleMiddleware::class]);
    $router->get('/faq', [\App\Controllers\Frontend\FaqController::class, 'index']);
    $router->get('/calculator', [\App\Controllers\Frontend\CalculatorController::class, 'index']);
    $router->get('/export-calculator', [\App\Controllers\Frontend\CalculatorController::class, 'index']);
    $router->get('/sitemap.xml', [\App\Controllers\Frontend\SitemapController::class, 'generate']);
    $router->get('/robots.txt', [\App\Controllers\Frontend\SitemapController::class, 'robotsTxt']);
    $router->get('/llms.txt', [\App\Controllers\Frontend\SitemapController::class, 'llmsTxt']);
    $router->get('/llms-full.txt', [\App\Controllers\Frontend\SitemapController::class, 'llmsFullTxt']);

    // Public Electronic Verification System (e-Invoice & Contract Verification)
    $router->get('/verify', [\App\Controllers\Frontend\VerifyController::class, 'index']);
    $router->get('/verify/invoice/{id}', [\App\Controllers\Frontend\VerifyController::class, 'invoice']);
    $router->get('/verify/invoice', [\App\Controllers\Frontend\VerifyController::class, 'invoice']);
    $router->get('/invoice/verify/{id}', [\App\Controllers\Frontend\VerifyController::class, 'invoice']);
    $router->get('/invoice/verify', [\App\Controllers\Frontend\VerifyController::class, 'invoice']);
    $router->get('/verify/contract/{id}', [\App\Controllers\Frontend\VerifyController::class, 'contract']);
    $router->get('/verify/contract', [\App\Controllers\Frontend\VerifyController::class, 'contract']);
    $router->get('/contract/verify/{id}', [\App\Controllers\Frontend\VerifyController::class, 'contract']);
    $router->get('/contract/verify', [\App\Controllers\Frontend\VerifyController::class, 'contract']);
    $router->get('/verify/certificate/{id}', [\App\Controllers\Frontend\VerifyController::class, 'certificate']);
    $router->get('/verify/certificate', [\App\Controllers\Frontend\VerifyController::class, 'certificate']);

    // ==========================================
    // 2. Admin Authentication Routes
    // ==========================================
    $router->get('/admin/login', [\App\Controllers\Admin\AuthController::class, 'showLogin'], [GuestMiddleware::class]);
    $router->post('/admin/login', [\App\Controllers\Admin\AuthController::class, 'login'], [GuestMiddleware::class, ThrottleMiddleware::class]);
    $router->post('/admin/login/submit', [\App\Controllers\Admin\AuthController::class, 'login'], [GuestMiddleware::class, ThrottleMiddleware::class]);
    $router->get('/admin/logout', [\App\Controllers\Admin\AuthController::class, 'logout']);

    // ==========================================
    // 3. Admin Panel Management Routes (Protected)
    // ==========================================
    $router->get('/admin', [\App\Controllers\Admin\DashboardController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/dashboard', [\App\Controllers\Admin\DashboardController::class, 'index'], [AuthMiddleware::class]);

    // Export Sections Management
    $router->get('/admin/sections', [\App\Controllers\Admin\SectionController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/sections/create', [\App\Controllers\Admin\SectionController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/admin/sections/store', [\App\Controllers\Admin\SectionController::class, 'store'], [AuthMiddleware::class]);
    $router->get('/admin/sections/edit/{id}', [\App\Controllers\Admin\SectionController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/admin/sections/{id}/edit', [\App\Controllers\Admin\SectionController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/admin/sections/edit/{id}', [\App\Controllers\Admin\SectionController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/sections/{id}/edit', [\App\Controllers\Admin\SectionController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/sections/update/{id}', [\App\Controllers\Admin\SectionController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/sections/{id}/update', [\App\Controllers\Admin\SectionController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/sections/delete/{id}', [\App\Controllers\Admin\SectionController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/sections/{id}/delete', [\App\Controllers\Admin\SectionController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/sections/toggle/{id}', [\App\Controllers\Admin\SectionController::class, 'toggleVisibility'], [AuthMiddleware::class]);
    $router->post('/admin/sections/{id}/toggle', [\App\Controllers\Admin\SectionController::class, 'toggleVisibility'], [AuthMiddleware::class]);

    // Products Management
    $router->get('/admin/products', [\App\Controllers\Admin\ProductController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/products/create', [\App\Controllers\Admin\ProductController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/admin/products/store', [\App\Controllers\Admin\ProductController::class, 'store'], [AuthMiddleware::class]);
    $router->get('/admin/products/edit/{id}', [\App\Controllers\Admin\ProductController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/admin/products/{id}/edit', [\App\Controllers\Admin\ProductController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/admin/products/edit/{id}', [\App\Controllers\Admin\ProductController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/products/{id}/edit', [\App\Controllers\Admin\ProductController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/products/update/{id}', [\App\Controllers\Admin\ProductController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/products/{id}/update', [\App\Controllers\Admin\ProductController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/products/delete/{id}', [\App\Controllers\Admin\ProductController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/products/{id}/delete', [\App\Controllers\Admin\ProductController::class, 'delete'], [AuthMiddleware::class]);

    // Media Library
    $router->get('/admin/media', [\App\Controllers\Admin\MediaController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/admin/media/upload', [\App\Controllers\Admin\MediaController::class, 'upload'], [AuthMiddleware::class]);
    $router->post('/admin/media/delete/{id}', [\App\Controllers\Admin\MediaController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/media/{id}/delete', [\App\Controllers\Admin\MediaController::class, 'delete'], [AuthMiddleware::class]);
    $router->get('/admin/media/json', [\App\Controllers\Admin\MediaController::class, 'jsonList'], [AuthMiddleware::class]);

    // Pages Management
    $router->get('/admin/pages', [\App\Controllers\Admin\PageController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/pages/create', [\App\Controllers\Admin\PageController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/admin/pages/store', [\App\Controllers\Admin\PageController::class, 'store'], [AuthMiddleware::class]);
    $router->get('/admin/pages/edit/{id}', [\App\Controllers\Admin\PageController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/admin/pages/{id}/edit', [\App\Controllers\Admin\PageController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/admin/pages/edit/{id}', [\App\Controllers\Admin\PageController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/pages/{id}/edit', [\App\Controllers\Admin\PageController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/pages/update/{id}', [\App\Controllers\Admin\PageController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/pages/{id}/update', [\App\Controllers\Admin\PageController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/pages/delete/{id}', [\App\Controllers\Admin\PageController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/pages/{id}/delete', [\App\Controllers\Admin\PageController::class, 'delete'], [AuthMiddleware::class]);

    // Posts / Blog Management
    $router->get('/admin/posts', [\App\Controllers\Admin\PostController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/posts/create', [\App\Controllers\Admin\PostController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/admin/posts/store', [\App\Controllers\Admin\PostController::class, 'store'], [AuthMiddleware::class]);
    $router->get('/admin/posts/edit/{id}', [\App\Controllers\Admin\PostController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/admin/posts/{id}/edit', [\App\Controllers\Admin\PostController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/admin/posts/edit/{id}', [\App\Controllers\Admin\PostController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/posts/{id}/edit', [\App\Controllers\Admin\PostController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/posts/update/{id}', [\App\Controllers\Admin\PostController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/posts/{id}/update', [\App\Controllers\Admin\PostController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/posts/delete/{id}', [\App\Controllers\Admin\PostController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/posts/{id}/delete', [\App\Controllers\Admin\PostController::class, 'delete'], [AuthMiddleware::class]);

    // Invoices Management (Proforma & Commercial)
    $router->get('/admin/invoices', [\App\Controllers\Admin\InvoiceController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/invoices/create', [\App\Controllers\Admin\InvoiceController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/admin/invoices/store', [\App\Controllers\Admin\InvoiceController::class, 'store'], [AuthMiddleware::class]);
    $router->get('/admin/invoices/show/{id}', [\App\Controllers\Admin\InvoiceController::class, 'show'], [AuthMiddleware::class]);
    $router->get('/admin/invoices/{id}', [\App\Controllers\Admin\InvoiceController::class, 'show'], [AuthMiddleware::class]);
    $router->get('/admin/invoices/{id}/show', [\App\Controllers\Admin\InvoiceController::class, 'show'], [AuthMiddleware::class]);
    $router->get('/admin/invoices/print/{id}', [\App\Controllers\Admin\InvoiceController::class, 'print'], [AuthMiddleware::class]);
    $router->get('/admin/invoices/{id}/print', [\App\Controllers\Admin\InvoiceController::class, 'print'], [AuthMiddleware::class]);
    $router->get('/admin/invoices/edit/{id}', [\App\Controllers\Admin\InvoiceController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/admin/invoices/{id}/edit', [\App\Controllers\Admin\InvoiceController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/admin/invoices/edit/{id}', [\App\Controllers\Admin\InvoiceController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/invoices/{id}/edit', [\App\Controllers\Admin\InvoiceController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/invoices/update/{id}', [\App\Controllers\Admin\InvoiceController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/invoices/{id}/update', [\App\Controllers\Admin\InvoiceController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/invoices/delete/{id}', [\App\Controllers\Admin\InvoiceController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/invoices/{id}/delete', [\App\Controllers\Admin\InvoiceController::class, 'delete'], [AuthMiddleware::class]);

    // Contracts Management (Export Sales Agreements)
    $router->get('/admin/contracts', [\App\Controllers\Admin\ContractController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/contracts/create', [\App\Controllers\Admin\ContractController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/admin/contracts/store', [\App\Controllers\Admin\ContractController::class, 'store'], [AuthMiddleware::class]);
    $router->get('/admin/contracts/show/{id}', [\App\Controllers\Admin\ContractController::class, 'show'], [AuthMiddleware::class]);
    $router->get('/admin/contracts/{id}', [\App\Controllers\Admin\ContractController::class, 'show'], [AuthMiddleware::class]);
    $router->get('/admin/contracts/{id}/show', [\App\Controllers\Admin\ContractController::class, 'show'], [AuthMiddleware::class]);
    $router->get('/admin/contracts/print/{id}', [\App\Controllers\Admin\ContractController::class, 'print'], [AuthMiddleware::class]);
    $router->get('/admin/contracts/{id}/print', [\App\Controllers\Admin\ContractController::class, 'print'], [AuthMiddleware::class]);
    $router->get('/admin/contracts/edit/{id}', [\App\Controllers\Admin\ContractController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/admin/contracts/{id}/edit', [\App\Controllers\Admin\ContractController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/admin/contracts/edit/{id}', [\App\Controllers\Admin\ContractController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/contracts/{id}/edit', [\App\Controllers\Admin\ContractController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/contracts/update/{id}', [\App\Controllers\Admin\ContractController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/contracts/{id}/update', [\App\Controllers\Admin\ContractController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/contracts/delete/{id}', [\App\Controllers\Admin\ContractController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/contracts/{id}/delete', [\App\Controllers\Admin\ContractController::class, 'delete'], [AuthMiddleware::class]);

    // Sliders & Carousel Management (Mam OS Engine)
    $router->get('/admin/sliders', [\App\Controllers\Admin\SliderController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/sliders/create', [\App\Controllers\Admin\SliderController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/admin/sliders/store', [\App\Controllers\Admin\SliderController::class, 'store'], [AuthMiddleware::class]);
    $router->post('/admin/sliders/autogenerate', [\App\Controllers\Admin\SliderController::class, 'autoGenerate'], [AuthMiddleware::class]);
    $router->get('/admin/sliders/edit/{id}', [\App\Controllers\Admin\SliderController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/admin/sliders/{id}/edit', [\App\Controllers\Admin\SliderController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/admin/sliders/edit/{id}', [\App\Controllers\Admin\SliderController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/sliders/{id}/edit', [\App\Controllers\Admin\SliderController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/sliders/update/{id}', [\App\Controllers\Admin\SliderController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/sliders/{id}/update', [\App\Controllers\Admin\SliderController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/sliders/delete/{id}', [\App\Controllers\Admin\SliderController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/sliders/{id}/delete', [\App\Controllers\Admin\SliderController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/sliders/toggle/{id}', [\App\Controllers\Admin\SliderController::class, 'toggle'], [AuthMiddleware::class]);
    $router->post('/admin/sliders/{id}/toggle', [\App\Controllers\Admin\SliderController::class, 'toggle'], [AuthMiddleware::class]);

    // Quote Requests
    $router->get('/admin/quotes', [\App\Controllers\Admin\QuoteController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/quotes/print/{id}', [\App\Controllers\Admin\QuoteController::class, 'printQuote'], [AuthMiddleware::class]);
    $router->get('/admin/quotes/{id}', [\App\Controllers\Admin\QuoteController::class, 'show'], [AuthMiddleware::class]);
    $router->get('/admin/quotes/show/{id}', [\App\Controllers\Admin\QuoteController::class, 'show'], [AuthMiddleware::class]);
    $router->post('/admin/quotes/{id}/status', [\App\Controllers\Admin\QuoteController::class, 'updateStatus'], [AuthMiddleware::class]);
    $router->post('/admin/quotes/status/{id}', [\App\Controllers\Admin\QuoteController::class, 'updateStatus'], [AuthMiddleware::class]);
    $router->post('/admin/quotes/delete/{id}', [\App\Controllers\Admin\QuoteController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/quotes/{id}/delete', [\App\Controllers\Admin\QuoteController::class, 'delete'], [AuthMiddleware::class]);

    // Contact Messages
    $router->get('/admin/messages', [\App\Controllers\Admin\ContactController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/messages/{id}', [\App\Controllers\Admin\ContactController::class, 'show'], [AuthMiddleware::class]);
    $router->get('/admin/messages/show/{id}', [\App\Controllers\Admin\ContactController::class, 'show'], [AuthMiddleware::class]);
    $router->post('/admin/messages/{id}/status', [\App\Controllers\Admin\ContactController::class, 'updateStatus'], [AuthMiddleware::class]);
    $router->post('/admin/messages/status/{id}', [\App\Controllers\Admin\ContactController::class, 'updateStatus'], [AuthMiddleware::class]);
    $router->post('/admin/messages/delete/{id}', [\App\Controllers\Admin\ContactController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/messages/{id}/delete', [\App\Controllers\Admin\ContactController::class, 'delete'], [AuthMiddleware::class]);

    // Users Management
    $router->get('/admin/users', [\App\Controllers\Admin\UserController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/users/create', [\App\Controllers\Admin\UserController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/admin/users/store', [\App\Controllers\Admin\UserController::class, 'store'], [AuthMiddleware::class]);
    $router->get('/admin/users/edit/{id}', [\App\Controllers\Admin\UserController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/admin/users/{id}/edit', [\App\Controllers\Admin\UserController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/admin/users/edit/{id}', [\App\Controllers\Admin\UserController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/users/{id}/edit', [\App\Controllers\Admin\UserController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/users/update/{id}', [\App\Controllers\Admin\UserController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/users/{id}/update', [\App\Controllers\Admin\UserController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/users/delete/{id}', [\App\Controllers\Admin\UserController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/users/{id}/delete', [\App\Controllers\Admin\UserController::class, 'delete'], [AuthMiddleware::class]);

    // Website Settings
    $router->get('/admin/settings', [\App\Controllers\Admin\SettingController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/admin/settings/update', [\App\Controllers\Admin\SettingController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/settings/test-email', [\App\Controllers\Admin\SettingController::class, 'testEmail'], [AuthMiddleware::class]);

    // SEO & Webmaster Suite
    $router->get('/admin/seo', [\App\Controllers\Admin\SeoController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/admin/seo/update', [\App\Controllers\Admin\SeoController::class, 'update'], [AuthMiddleware::class]);

    // Certificates & Accreditations Management
    $router->get('/admin/certificates', [\App\Controllers\Admin\CertificateController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/certificates/create', [\App\Controllers\Admin\CertificateController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/admin/certificates/store', [\App\Controllers\Admin\CertificateController::class, 'store'], [AuthMiddleware::class]);
    $router->get('/admin/certificates/edit/{id}', [\App\Controllers\Admin\CertificateController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/admin/certificates/{id}/edit', [\App\Controllers\Admin\CertificateController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/admin/certificates/edit/{id}', [\App\Controllers\Admin\CertificateController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/certificates/{id}/edit', [\App\Controllers\Admin\CertificateController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/certificates/update/{id}', [\App\Controllers\Admin\CertificateController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/certificates/{id}/update', [\App\Controllers\Admin\CertificateController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/certificates/delete/{id}', [\App\Controllers\Admin\CertificateController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/certificates/{id}/delete', [\App\Controllers\Admin\CertificateController::class, 'delete'], [AuthMiddleware::class]);

    // FAQs Management
    $router->get('/admin/faqs', [\App\Controllers\Admin\FaqController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/faqs/create', [\App\Controllers\Admin\FaqController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/admin/faqs/store', [\App\Controllers\Admin\FaqController::class, 'store'], [AuthMiddleware::class]);
    $router->get('/admin/faqs/edit/{id}', [\App\Controllers\Admin\FaqController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/admin/faqs/{id}/edit', [\App\Controllers\Admin\FaqController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/admin/faqs/edit/{id}', [\App\Controllers\Admin\FaqController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/faqs/{id}/edit', [\App\Controllers\Admin\FaqController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/faqs/update/{id}', [\App\Controllers\Admin\FaqController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/faqs/{id}/update', [\App\Controllers\Admin\FaqController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/admin/faqs/delete/{id}', [\App\Controllers\Admin\FaqController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/admin/faqs/{id}/delete', [\App\Controllers\Admin\FaqController::class, 'delete'], [AuthMiddleware::class]);

    // Navigation Menus Builder
    $router->get('/admin/menus', [\App\Controllers\Admin\MenuController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/admin/menus/update', [\App\Controllers\Admin\MenuController::class, 'update'], [AuthMiddleware::class]);

    // Database & Backups Management
    $router->get('/admin/backups', [\App\Controllers\Admin\BackupController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/backups/export', [\App\Controllers\Admin\BackupController::class, 'export'], [AuthMiddleware::class]);
    $router->post('/admin/backups/optimize', [\App\Controllers\Admin\BackupController::class, 'optimize'], [AuthMiddleware::class]);

    // Themes Engine (WordPress Style)
    $router->get('/admin/themes', [\App\Controllers\Admin\ThemeController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/admin/themes/activate', [\App\Controllers\Admin\ThemeController::class, 'activate'], [AuthMiddleware::class]);
    $router->post('/admin/themes/activate/{slug}', [\App\Controllers\Admin\ThemeController::class, 'activate'], [AuthMiddleware::class]);
    $router->get('/admin/themes/preview/{slug}', [\App\Controllers\Admin\ThemeController::class, 'preview'], [AuthMiddleware::class]);
    $router->get('/admin/themes/exit-preview', [\App\Controllers\Admin\ThemeController::class, 'exitPreview'], [AuthMiddleware::class]);
    $router->get('/admin/themes/customize/{slug}', [\App\Controllers\Admin\ThemeController::class, 'customize'], [AuthMiddleware::class]);
    $router->post('/admin/themes/customize/{slug}', [\App\Controllers\Admin\ThemeController::class, 'saveCustomization'], [AuthMiddleware::class]);

    // Activity Logs
    $router->get('/admin/logs', [\App\Controllers\Admin\ActivityLogController::class, 'index'], [AuthMiddleware::class]);

    // System, Domain & Database Installation Wizard
    $router->get('/admin/system/setup', [\App\Controllers\Admin\SetupController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/installer', [\App\Controllers\Admin\SetupController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/admin/system/setup/domain', [\App\Controllers\Admin\SetupController::class, 'saveDomain'], [AuthMiddleware::class]);
    $router->post('/admin/system/setup/migration', [\App\Controllers\Admin\SetupController::class, 'runMigration'], [AuthMiddleware::class]);
    $router->get('/admin/system/health', [\App\Controllers\Admin\SetupController::class, 'health'], [AuthMiddleware::class]);
    $router->post('/admin/system/cache/clear', [\App\Controllers\Admin\SetupController::class, 'clearCache'], [AuthMiddleware::class]);
    $router->post('/admin/system/db/optimize', [\App\Controllers\Admin\SetupController::class, 'optimizeDb'], [AuthMiddleware::class]);

    // Export Analytics & Reports
    $router->get('/admin/reports', [\App\Controllers\Admin\ReportController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/admin/reports/export-csv', [\App\Controllers\Admin\ReportController::class, 'exportCsv'], [AuthMiddleware::class]);
}
}
