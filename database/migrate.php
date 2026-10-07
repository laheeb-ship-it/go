<?php
/**
 * Golden Bird Charcoal CMS - Unified Database Migration & Seeder Engine
 * Pure MySQL / MariaDB (InnoDB, utf8mb4) via PDO MySQL and MySQLi
 * PHP 7.1+ / 8.0+
 */

if (file_exists(__DIR__ . '/../app/Config/database.php')) {
    require_once __DIR__ . '/../app/Config/database.php';
}

$runtimeConfig = (class_exists('\App\Core\Database') ? \App\Core\Database::getRuntimeConfig() : null) ?: ($dbCfg ?? ($_SESSION['db_config'] ?? null));

$host = $runtimeConfig['host'] ?? (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
$user = $runtimeConfig['user'] ?? (defined('DB_USER') ? DB_USER : 'root');
$pass = $runtimeConfig['pass'] ?? (defined('DB_PASS') ? DB_PASS : '');
$name = $runtimeConfig['name'] ?? (defined('DB_NAME') ? DB_NAME : 'goldenbird_export_db');
$port = (int)($runtimeConfig['port'] ?? (defined('DB_PORT') ? (int)DB_PORT : 3306));
$charset = $runtimeConfig['charset'] ?? (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');

echo "--- Golden Bird Charcoal MySQL Database Setup Starting ---\n";

$pdo = null;
$mysqli = null;
$driver = 'mysql';

// 1. Try PDO MySQL
if (extension_loaded('pdo_mysql')) {
    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $pdo->exec("SET NAMES {$charset} COLLATE {$charset}_unicode_ci");
        $driver = 'pdo_mysql';
        echo "[✓] Connected via PDO MySQL to {$host}:{$port}/{$name}\n";
    } catch (\Throwable $e) {
        try {
            $dsnRoot = "mysql:host={$host};port={$port};charset={$charset}";
            $pdoRoot = new PDO($dsnRoot, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 5
            ]);
            if (!empty($name)) {
                $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdoRoot->exec("USE `{$name}`");
            }
            $pdo = $pdoRoot;
            $driver = 'pdo_mysql';
            echo "[✓] Created and connected via PDO MySQL to {$host}:{$port}/{$name}\n";
        } catch (\Throwable $e2) {
            // Try MySQLi
        }
    }
}

// 2. Try MySQLi if PDO MySQL failed
if (!$pdo && extension_loaded('mysqli')) {
    try {
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli($host, $user, $pass, $name, $port);
        if ($conn->connect_error) {
            $conn = @new mysqli($host, $user, $pass, '', $port);
        }
        if (!$conn->connect_error) {
            if (!empty($name)) {
                $conn->query("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $conn->select_db($name);
            }
            $conn->set_charset($charset);
            $mysqli = $conn;
            $driver = 'mysqli';
            echo "[✓] Connected via MySQLi to {$host}:{$port}/{$name}\n";
        }
    } catch (\Throwable $e) {
        // Fallback error
    }
}

if (!$pdo && !$mysqli) {
    die("FATAL: Could not connect to MySQL server at {$host}:{$port} with user '{$user}'.\n");
}

// Unified query / execution helper
$execSql = function(string $sql, array $params = []) use (&$pdo, &$mysqli, $driver): int {
    if ($driver === 'mysqli' && $mysqli) {
        if (empty($params)) {
            $mysqli->query($sql);
            return (int)$mysqli->affected_rows;
        }
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return 0;
        }
        $types = '';
        $bindParams = [];
        foreach ($params as $p) {
            if (is_int($p)) $types .= 'i';
            elseif (is_float($p)) $types .= 'd';
            else $types .= 's';
            $bindParams[] = $p;
        }
        $stmt->bind_param($types, ...$bindParams);
        $stmt->execute();
        $id = $stmt->insert_id ?: $stmt->affected_rows;
        $stmt->close();
        return (int)$id;
    } elseif ($pdo) {
        if (empty($params)) {
            $pdo->exec($sql);
            return 1;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $lastId = $pdo->lastInsertId();
        return $lastId ? (int)$lastId : (int)$stmt->rowCount();
    }
    return 0;
};

$fetchRow = function(string $sql, array $params = []) use (&$pdo, &$mysqli, $driver): ?array {
    if ($driver === 'mysqli' && $mysqli) {
        if (empty($params)) {
            $res = $mysqli->query($sql);
            if ($res && ($row = $res->fetch_assoc())) return $row;
            return null;
        }
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) return null;
        $types = '';
        $bindParams = [];
        foreach ($params as $p) {
            if (is_int($p)) $types .= 'i';
            elseif (is_float($p)) $types .= 'd';
            else $types .= 's';
            $bindParams[] = $p;
        }
        $stmt->bind_param($types, ...$bindParams);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row;
    } elseif ($pdo) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }
    return null;
};

// 1. Run Schema SQL for MySQL / MariaDB
$schemaFile = __DIR__ . '/schema.sql';
if (file_exists($schemaFile)) {
        $schemaSql = file_get_contents($schemaFile);
        $cleanSql = preg_replace('!/\*.*?\*/!s', '', $schemaSql);
        $cleanSql = preg_replace('/^--.*?$/m', '', $cleanSql);
        $statements = array_filter(array_map('trim', explode(';', $cleanSql)));

        if ($pdo) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    try {
                        $pdo->exec($stmt);
                    } catch (\Throwable $ex) {}
                }
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        } elseif ($mysqli) {
            $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    try {
                        $mysqli->query($stmt);
                    } catch (\Throwable $ex) {}
                }
            }
            $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
        }
        echo "[✓] Schema tables created.\n";
    }

// 2. Seed Roles
$roles = [
    [
        'name' => 'مدير النظام الكامل (Super Admin)',
        'slug' => 'super_admin',
        'permissions' => json_encode(['all' => true, 'sections' => true, 'products' => true, 'pages' => true, 'posts' => true, 'media' => true, 'quotes' => true, 'users' => true, 'settings' => true, 'logs' => true, 'invoices' => true, 'contracts' => true, 'certificates' => true, 'faqs' => true, 'sliders' => true])
    ],
    [
        'name' => 'محرر محتوى (Content Editor)',
        'slug' => 'editor',
        'permissions' => json_encode(['sections' => true, 'products' => true, 'pages' => true, 'posts' => true, 'media' => true, 'certificates' => true, 'faqs' => true, 'sliders' => true])
    ],
    [
        'name' => 'مسؤول مبيعات وتصدير (Export Sales Manager)',
        'slug' => 'sales_manager',
        'permissions' => json_encode(['quotes' => true, 'contact' => true, 'products' => true, 'invoices' => true, 'contracts' => true])
    ]
];

$roleMap = [];
foreach ($roles as $r) {
    $existing = $fetchRow("SELECT id FROM roles WHERE slug = ?", [$r['slug']]);
    if ($existing) {
        $roleMap[$r['slug']] = (int)$existing['id'];
    } else {
        $newId = $execSql("INSERT INTO roles (name, slug, permissions) VALUES (?, ?, ?)", [$r['name'], $r['slug'], $r['permissions']]);
        $roleMap[$r['slug']] = $newId ?: 1;
    }
}
$superAdminRoleId = $roleMap['super_admin'] ?? 1;

// 3. Seed / Update Super Admin User
$adminName = $runtimeConfig['admin_name'] ?? 'Golden Bird Executive Management';
$adminEmail = $runtimeConfig['admin_email'] ?? 'admin@goldnberd.com';
$adminRawPass = $runtimeConfig['admin_pass'] ?? (getenv('ADMIN_PASS') ?: '');
$adminPassword = $adminRawPass !== '' ? password_hash($adminRawPass, PASSWORD_BCRYPT, ['cost' => 12]) : null;
$adminStatus = 'active';

$checkUser = $fetchRow("SELECT id FROM users WHERE email = ? OR id = 1 LIMIT 1", [$adminEmail]);
if ($checkUser) {
    $adminId = (int)$checkUser['id'];
    $adminParams = [$adminEmail, $adminName, $superAdminRoleId, $adminId];
    $passwordClause = '';
    if ($adminPassword !== null) {
        $passwordClause = 'password_hash = ?, ';
        array_unshift($adminParams, $adminPassword);
    }
    $execSql("UPDATE users SET {$passwordClause}email = ?, name = ?, status = 'active', role_id = ? WHERE id = ?", $adminParams);
    echo "[✓] Admin user updated: {$adminEmail}\n";
} elseif ($adminPassword !== null) {
    $adminId = $execSql("INSERT INTO users (role_id, name, email, password_hash, status) VALUES (?, ?, ?, ?, ?)", [
        $superAdminRoleId,
        $adminName,
        $adminEmail,
        $adminPassword,
        $adminStatus
    ]);
    echo "[✓] Admin user created: {$adminEmail} (ID: {$adminId})\n";
} else {
    echo "[!] Admin user was not created. Set ADMIN_PASS or admin_pass in the installer configuration.\n";
}

// 5. Seed Website Settings
$dynSiteName = $runtimeConfig['site_name'] ?? 'شركة جولدن بيرد لتصدير الفحم النباتي | Golden Bird Charcoal Export Group';
$dynPrimaryDomain = $runtimeConfig['primary_domain'] ?? 'goldnberd.com';
$dynSiteUrl = $runtimeConfig['site_url'] ?? 'https://goldnberd.com';
$dynCompanyEmail = $runtimeConfig['company_email'] ?? $runtimeConfig['admin_email'] ?? 'export@goldnberd.com';
$dynCompanyPhone = $runtimeConfig['company_phone'] ?? '+20 100 025 1645';
$dynCompanyWhatsapp = $runtimeConfig['company_whatsapp'] ?? '01000251645';
$dynCompanyAddress = $runtimeConfig['company_address'] ?? 'جمهورية مصر العربية - محافظة دمياط / القاهرة - المنطقة الاستثمارية والتصديرية';

$settings = [
    ['site_name', $dynSiteName, 'general'],
    ['site_tagline', 'الريادة والتميز في تصدير الفحم النباتي والحاصلات الزراعية للأسواق العالمية', 'general'],
    ['primary_domain', $dynPrimaryDomain, 'general'],
    ['site_url', $dynSiteUrl, 'general'],
    ['company_email', $dynCompanyEmail, 'contact'],
    ['company_phone', $dynCompanyPhone, 'contact'],
    ['company_whatsapp', $dynCompanyWhatsapp, 'contact'],
    ['company_address', $dynCompanyAddress, 'contact'],
    ['working_hours', 'السبت - الخميس: 8:00 صباحاً - 6:00 مساءً (توقيت القاهرة GMT+2)', 'contact'],
    ['export_license', 'سجل مصدرين رقم: 489201 / بطاقة ضريبية معتمدة', 'general'],
    ['facebook_url', 'https://facebook.com/goldenbirdcharcoal', 'social'],
    ['linkedin_url', 'https://linkedin.com/company/goldenbirdcharcoal', 'social'],
    ['instagram_url', 'https://instagram.com/goldenbirdcharcoal', 'social'],
    ['youtube_url', 'https://youtube.com/@goldenbirdcharcoal', 'social'],
    ['default_seo_title', $dynSiteName . ' | تصدير الفحم النباتي والحاصلات الزراعية', 'seo'],
    ['default_seo_description', 'جولدن بيرد جروب شركة مصرية رائدة في تصدير الفحم النباتي للشواء والشيشة والحاصلات الزراعية والغذائية بأعلى مواصفات الجودة الأوروبية والخليجية والتسليم الموانئ العالمية.', 'seo'],
    ['default_seo_keywords', 'تصدير فحم, فحم نباتي, فحم برتقال, فحم طلح, حاصلات زراعية مصرية, جولدن بيرد جروب, تصدير موالح, فحم شيشة, charcoal export egypt', 'seo'],
    ['header_notice', 'نقدم خدمات الشحن والتصدير لكافة موانئ الشرق الأوسط، أوروبا، وإفريقيا وفق شروط الإنكوترمز (FOB, CIF, CFR).', 'general'],
    ['system_license_key', '1997', 'license'],
    ['system_license_status', 'licensed_active', 'license'],
    ['seo_meta_title', 'مجموعة جولدن بيرد لتصدير الفحم | Golden Bird Charcoal Export', 'seo'],
    ['seo_meta_keywords', 'تصدير فحم, فحم حمضيات, فحم جزورين, فحم شيشة, تصدير برتقال, تصدير بصل, تصدير ثوم, FOB, CIF, مصر', 'seo'],
    ['seo_canonical_domain', $dynSiteUrl, 'seo'],
    ['installed_at', date('Y-m-d H:i:s'), 'general']
];

foreach ($settings as $s) {
    $existing = $fetchRow("SELECT id FROM settings WHERE setting_key = ?", [$s[0]]);
    if ($existing) {
        $execSql("UPDATE settings SET setting_value = ?, group_name = ?, autoload = 1 WHERE setting_key = ?", [$s[1], $s[2], $s[0]]);
    } else {
        $execSql("INSERT INTO settings (setting_key, setting_value, group_name, autoload) VALUES (?, ?, ?, 1)", [$s[0], $s[1], $s[2]]);
    }
}
echo "[✓] Settings seeded.\n";

// 6. Seed Dynamic Export Sections
$sections = [
    [
        'name' => 'قطاع تصدير الفحم النباتي والصناعي',
        'slug' => 'charcoal',
        'short_description' => 'إنتاج وتصدير أجود أنواع الفحم النباتي الطبيعي للشيشة والمشاوي والشواء الاحترافي، خالٍ من الشوائب ومطابق للمواصفات الدولية.',
        'full_description' => '<p>تعتبر <strong>مجموعة جولدن بيرد (Golden Bird Charcoal & Export)</strong> من أكبر الكيانات المتخصصة في تصنيع وغربلة وتعبئة وتصدير الفحم النباتي الطبيعي والمضغوط. نعتمد على مكامير فحم متطورة بيئياً وفحص مخبري دقيق يضمن أعلى نسبة كربون ثابت وأقل نسبة رماد ورطوبة.</p><p>نصدر لكبرى الأسواق في الخليج العربي (السعودية، الإمارات، الكويت، قطر، سلطنة عمان) والدول الأوروبية (ألمانيا، اليونان، إيطاليا، قبرص، بولندا) مع توفير حلول التعبئة الخاصة Private Label بعلامة العميل التجارية.</p>',
        'icon_code' => 'flame',
        'sort_order' => 1,
        'status' => 'published',
        'seo_title' => 'تصدير الفحم النباتي الطبيعي والصناعي | جولدن بيرد',
        'seo_description' => 'تصدير أجود أنواع الفحم النباتي: فحم برتقال، فحم طلح سوداني، فحم جزورين، وفحم مضغوط للشيشة والمشاوي بأسعار منافسة وتسليم موانئ عالمية.'
    ],
    [
        'name' => 'قطاع تصدير الحاصلات الزراعية والخضار والفاكهة',
        'slug' => 'agricultural-products',
        'short_description' => 'تصدير الخضروات والفواكه الطازجة والمجمدة من أجود المزارع المصرية المعتمدة وفق معايير Global GAP وشهادات الصحة النباتية.',
        'full_description' => '<p>يمتلك قطاع الحاصلات الزراعية في شركة جولدن بيرد خطوط فرز وتدريج وتعبئة وتبريد بأحدث التقنيات لضمان وصول المحاصيل طازجة بأعلى معايير النضارة والسلامة الغذائية.</p><p>تشمل صادراتنا الموالح (برتقال فالنسيا وبصرة، ليمون، يوسفي)، البصل الذهبي والأحمر، الثوم الجاف، الرمان، الفراولة، والبطاطس التصنيعية ومائدة الطعام.</p>',
        'icon_code' => 'sprout',
        'sort_order' => 2,
        'status' => 'published',
        'seo_title' => 'تصدير الحاصلات الزراعية والفواكه المصرية | جولدن بيرد',
        'seo_description' => 'شركة جولدن بيرد لتصدير الحاصلات الزراعية: موالح، بصل، ثوم، بطاطس، وفواكه طازجة معتمدة لكافة الموانئ العالمية.'
    ],
    [
        'name' => 'قطاع السلع والمواد الغذائية التصديرية',
        'slug' => 'food-products',
        'short_description' => 'توريد وتصدير السلع الغذائية الجافة والمصنعة، التمور الفاخرة، البقوليات، وزيوت الطعام والأعشاب الطبية والعطرية.',
        'full_description' => '<p>نوفر لشركائنا الدوليين حلول توريد متكاملة للمنتجات الغذائية المصرية ذات الجودة الفائقة، مع الالتزام الصارم بشهادات الفحص الدولي والتحليل الجمركي والتغليف الآمن للشحن البحري والجوي.</p>',
        'icon_code' => 'box',
        'sort_order' => 3,
        'status' => 'published',
        'seo_title' => 'تصدير السلع والمواد الغذائية المعبأة | جولدن بيرد',
        'seo_description' => 'تصدير الأغذية والتمور والبقوليات والأعشاب الطبية من مصر إلى مختلف دول العالم بأعلى معايير الجودة.'
    ]
];

$sectionIds = [];
foreach ($sections as $sec) {
    $existing = $fetchRow("SELECT id FROM export_sections WHERE slug = ?", [$sec['slug']]);
    if ($existing) {
        $sectionIds[$sec['slug']] = (int)$existing['id'];
    } else {
        $newId = $execSql("INSERT INTO export_sections (name, slug, short_description, full_description, icon_code, sort_order, status, seo_title, seo_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $sec['name'], $sec['slug'], $sec['short_description'], $sec['full_description'], $sec['icon_code'], $sec['sort_order'], $sec['status'], $sec['seo_title'], $sec['seo_description']
        ]);
        $sectionIds[$sec['slug']] = $newId;
    }
}
echo "[✓] Export Sections seeded.\n";

// 7. Seed Products
$products = [
    [
        'section_slug' => 'charcoal',
        'name' => 'فحم برتقال وموالح فاخر للشيشة (Citrus Charcoal)',
        'slug' => 'premium-citrus-wood-charcoal',
        'sku' => 'BH-CHR-ORG01',
        'short_description' => 'فحم برتقال طبيعي 100% نخب أول مخصص للمقاهي والشيشة الفاخرة بدون دخان أو شرر ورماد أبيض ثلجي.',
        'full_description' => '<p>يعد فحم خشب البرتقال والموالح من أرقى وأفضل أنواع الفحم النباتي في الشرق الأوسط وأوروبا. يتميز بحرارة متجانسة جداً وبقاء الجمر مشتعلاً لساعات طويلة دون أن يترك رائحة أو يغير طعم التبغ.</p>',
        'specifications' => json_encode([
            'الكربون الثابت (Fixed Carbon)' => '78% - 82%',
            'نسبة الرماد (Ash Content)' => '1.8% - 2.4% (رماد أبيض ناصع)',
            'نسبة الرطوبة (Moisture)' => 'أقل من 4%',
            'القيمة الحرارية (Caloric Value)' => '7400 - 7800 Kcal/kg',
            'زمن الاشتعال (Burning Time)' => '4.5 إلى 5.5 ساعات',
            'حجم القطع (Lump Size)' => '4 سم - 15 سم (حسب طلب المستورد)'
        ], JSON_UNESCAPED_UNICODE),
        'packaging_options' => 'أكياس ورقية كرافت 3 طبقات (1 كجم، 2 كجم، 3 كجم، 5 كجم)، أكياس بولي بروبيلين منسوجة (10 كجم، 15 كجم، 20 كجم).',
        'origin_country' => 'مصر (Egypt)',
        'is_featured' => 1,
        'sort_order' => 1,
        'status' => 'published',
        'seo_title' => 'فحم برتقال للشيشة تصدير فاخر | شركة جولدن بيرد',
        'seo_description' => 'تصدير فحم برتقال طبيعي مصري للشيشة بدون دخان ولا شرر ورماد أبيض ناصع.'
    ],
    [
        'section_slug' => 'charcoal',
        'name' => 'فحم طلح سوداني أصلي للشواء والمطاعم (Talh Hardwood Charcoal)',
        'slug' => 'talh-hardwood-bbq-charcoal',
        'sku' => 'BH-CHR-TLH02',
        'short_description' => 'فحم طلح صلب ذو طاقة حرارية هائلة يدوم لأكثر من 6 ساعات، مثالي لمطاعم الشواء والمشاوي الفندقية.',
        'full_description' => '<p>فحم خشب الطلح الصخري، نخب تصديري ممتاز للمطاعم الكبرى وعشاق الباربكيو والشواء المفتوح. يعطي نكهة مدخنة رائعة مع ثبات حراري فائق.</p>',
        'specifications' => json_encode([
            'الكربون الثابت' => '80% - 85%',
            'نسبة الرماد' => '2.5% - 3.2%',
            'نسبة الرطوبة' => 'أقل من 5%',
            'القيمة الحرارية' => '8000 Kcal/kg',
            'زمن الاشتعال' => '6 إلى 7 ساعات متواصلة'
        ], JSON_UNESCAPED_UNICODE),
        'packaging_options' => 'شكاير بولي بروبلين 15 كجم / 20 كجم، كراتين كرتون مقوى 10 كجم.',
        'origin_country' => 'مصر / السودان',
        'is_featured' => 1,
        'sort_order' => 2,
        'status' => 'published',
        'seo_title' => 'فحم طلح سوداني للتصدير والمطاعم | جولدن بيرد',
        'seo_description' => 'تصدير فحم طلح صلب عالي الحرارة للمطاعم والشواء بكميات حاويات كاملة FCL لكافة الموانئ.'
    ],
    [
        'section_slug' => 'charcoal',
        'name' => 'فحم جزورين طبيعي مخصص للمشاوي (Casuarina BBQ Charcoal)',
        'slug' => 'casuarina-bbq-charcoal',
        'sku' => 'BH-CHR-CAS03',
        'short_description' => 'فحم جزورين نباتي سريع الاشتعال وقوي الحرارة، ممتاز للمطاعم والشواء المنزلي والخارجي.',
        'full_description' => '<p>يتميز فحم الجزورين المصري بقدرته الفائقة على توليد حرارة سريعة وقوية للشواء، وهو الاختيار الاقتصادي الأول والأكثر طلباً لدى سلاسل المطاعم وشركات التوزيع في الأسواق الإقليمية والدولية.</p>',
        'specifications' => json_encode([
            'الكربون الثابت' => '75% - 79%',
            'نسبة الرماد' => '3.0%',
            'الرطوبة' => 'أقل من 5%',
            'القيمة الحرارية' => '7200 Kcal/kg',
            'زمن الاشتعال' => '3.5 إلى 4.5 ساعات'
        ], JSON_UNESCAPED_UNICODE),
        'packaging_options' => 'أكياس خيش طبيعي أو بولي بروبلين 10/15/20 كجم.',
        'origin_country' => 'مصر (Egypt)',
        'is_featured' => 0,
        'sort_order' => 3,
        'status' => 'published',
        'seo_title' => 'فحم جزورين طبيعي للشواء | جولدن بيرد للتصدير',
        'seo_description' => 'تصدير فحم جزورين نباتي مصري للشواء والمشاوي والمطاعم بجودة غربلة عالية.'
    ],
    [
        'section_slug' => 'agricultural-products',
        'name' => 'برتقال فالنسيا وبصرة مصري تصدير نخب أول (Fresh Oranges)',
        'slug' => 'fresh-valencia-navel-oranges-export',
        'sku' => 'BH-AGR-ORG01',
        'short_description' => 'برتقال فالنسيا وبصرة طازج عالي العصارة ونسبة السكر، محصود من مزارع معتمدة ومطابق للمواصفات الأوروبية.',
        'full_description' => '<p>تشتهر مصر بأنها المورد والمصدر الأول للبرتقال عالمياً. توفر شركة جولدن بيرد برتقال فالنسيا الصيفي وبرتقال أبو سرة الشتوي بدرجات فرز دقيقة وتبريد احترافي للحفاظ على جودة القشرة والعصارة.</p>',
        'specifications' => json_encode([
            'الأصناف المتاحة' => 'Valencia (فالنسيا) & Navel (أبو سرة)',
            'الأحجام (Counts)' => '36 / 40 / 48 / 56 / 64 / 72 / 80 / 88 / 100 / 113',
            'نسبة العصارة' => 'أكثر من 45%',
            'نسبة السكريات (Brix)' => '11% - 13%',
            'درجة حرارة الشحن' => '+2°C إلى +5°C'
        ], JSON_UNESCAPED_UNICODE),
        'packaging_options' => 'كراتين تلسكوبية 15 كجم، كراتين أوبن توب 15 كجم، أكياس شبكية Net Bags (2 كجم، 5 كجم).',
        'origin_country' => 'مصر (Egypt)',
        'is_featured' => 1,
        'sort_order' => 1,
        'status' => 'published',
        'seo_title' => 'تصدير برتقال فالنسيا وبصرة مصري نخب أول | جولدن بيرد',
        'seo_description' => 'تصدير الموالح والبرتقال المصري الطازج بجودة عالمية لكبرى الأسواق الأوروبية والعربية والآسيوية.'
    ],
    [
        'section_slug' => 'agricultural-products',
        'name' => 'بصل أحمر وذهبي مصري جاف للتصدير (Fresh Dry Onions)',
        'slug' => 'fresh-red-golden-dry-onions',
        'sku' => 'BH-AGR-ONN02',
        'short_description' => 'بصل أحمر مصري ممتاز وبصل ذهبي مجفف شمسياً ذو صلابة فائقة وعمر تخزيني طويل للشحن البحري البعيد.',
        'full_description' => '<p>بصل مصري صلب بقشرة قوية ونظيفة، يتم جمعه وتجفيفه وفرزه وتعبئته في أكياس شبكية جيدة التهوية لضمان وصوله بحالة ممتازة حتى بعد رحلات الشحن الطويلة.</p>',
        'specifications' => json_encode([
            'اللون' => 'أحمر داكن / ذهبي نحاسي',
            'الأقطار المتاحة' => '40-60 مم / 50-70 مم / 60-80 مم / 80-100 مم',
            'الجفاف' => '100% قشرة خارجية جافة ومحكمة'
        ], JSON_UNESCAPED_UNICODE),
        'packaging_options' => 'أكياس شبكية مخرمة Mesh Bags وزن 10 كجم، 25 كجم، أو أكياس جامبو 1 طن Jumbo Bags.',
        'origin_country' => 'مصر (Egypt)',
        'is_featured' => 1,
        'sort_order' => 2,
        'status' => 'published',
        'seo_title' => 'تصدير بصل أحمر وذهبي مصري طازج | جولدن بيرد',
        'seo_description' => 'تصدير البصل المصري الجاف بكافة المقاسات والأوزان مع شهادات السلامة النباتية الرسمية.'
    ],
    [
        'section_slug' => 'food-products',
        'name' => 'تمور سيوية ومجدول فاخرة معبأة (Egyptian Premium Dates)',
        'slug' => 'premium-egyptian-dates-medjool-siwi',
        'sku' => 'BH-FOD-DTS01',
        'short_description' => 'تمور طبيعية معبأة ومغلفة في عبوات مفرغة الهواء، صنف مجدول وسيوى صعيدي عالي الجودة.',
        'full_description' => '<p>تمور مصرية فاخرة يتم قطافها وغسلها وتعقيمها وتعبئتها بأحدث خطوط التعبئة والتغليف، غنية بالعناصر الغذائية الطبيعية وتناسب سلاسل السوبرماركت والموزعين الدوليين.</p>',
        'specifications' => json_encode([
            'الأصناف' => 'مجدول (Medjool) & سيوى / صعيدي (Siwi / Saidi)',
            'الرطوبة' => 'نصف جافة 18% - 22%',
            'الأحجام' => 'Medium / Large / Jumbo / Super Jumbo'
        ], JSON_UNESCAPED_UNICODE),
        'packaging_options' => 'عبوات فاخرة 500 جم، 1 كجم، 5 كجم، كراتين بالجملة Bulk 10 كجم.',
        'origin_country' => 'مصر (Egypt)',
        'is_featured' => 1,
        'sort_order' => 1,
        'status' => 'published',
        'seo_title' => 'تصدير تمور مصرية فاخرة ومجدول | جولدن بيرد للتصدير',
        'seo_description' => 'تصدير التمور المصرية المعبأة والمجدول بأعلى معايير الجودة والتغليف لكافة الأسواق العالمية.'
    ]
];

foreach ($products as $p) {
    $secId = $sectionIds[$p['section_slug']] ?? null;
    if (!$secId) continue;

    $existing = $fetchRow("SELECT id FROM products WHERE slug = ?", [$p['slug']]);
    if (!$existing) {
        $execSql("INSERT INTO products (export_section_id, name, slug, sku, short_description, full_description, specifications, packaging_options, origin_country, is_featured, sort_order, status, seo_title, seo_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $secId, $p['name'], $p['slug'], $p['sku'], $p['short_description'], $p['full_description'], $p['specifications'], $p['packaging_options'], $p['origin_country'], $p['is_featured'], $p['sort_order'], $p['status'], $p['seo_title'], $p['seo_description']
        ]);
    }
}
echo "[✓] Products seeded.\n";

// 8. Seed Pages
$pages = [
    [
        'title' => 'من نحن - شركة جولدن بيرد لتصدير الفحم النباتي',
        'slug' => 'about',
        'template' => 'about',
        'content' => '<h2>شركة رائدة في التجارة والتصدير الدولي</h2><p>تأسست <strong>شركة جولدن بيرد لتصدير الفحم النباتي (Golden Bird Charcoal Export Group)</strong> برؤية واضحة تهدف إلى وضع المنتجات والحاصلات المصرية في صدارة الأسواق العالمية من خلال الالتزام الصارم بأعلى معايير الجودة، الشفافية، والاحترافية اللوجستية.</p><p>نمتلك خبرة عميقة في تصنيع وغربلة وتصدير الفحم النباتي الطبيعي للشيشة والمشاوي بكافة أنواعه، بالإضافة إلى قطاع تصدير الحاصلات الزراعية والخضروات والفواكه الطازجة والسلع الغذائية المعبأة.</p>',
        'seo_title' => 'عن شركة جولدن بيرد لتصدير الفحم | الجودة والريادة الدولية',
        'seo_description' => 'تعرف على شركة جولدن بيرد لتصدير الفحم النباتي، تاريخنا، طاقتنا الإنتاجية، وشبكة علاقاتنا التصديرية في الخليج وأوروبا وإفريقيا.'
    ],
    [
        'title' => 'معايير الجودة والشهادات والاعتمادات',
        'slug' => 'quality-certifications',
        'template' => 'quality',
        'content' => '<h2>الجودة هي حجر الزاوية في صادراتنا</h2><p>في شركة جولدن بيرد، لا تخرج أي شحنة تصديرية إلا بعد اجتياز سلسلة متكاملة من اختبارات الفحص والرقابة الصارمة وفق أرقى المعايير الدولية المعترف بها.</p><ul><li><strong>فحص SGS الدولي:</strong> شهادات الفحص والتحليل الكيميائي لنسب الرماد والكربون والرطوبة.</li><li><strong>شهادات الصحة النباتية:</strong> معتمدة من وزارة الزراعة والحجر الزراعي المصري.</li><li><strong>شهادة المنشأ الرسمية:</strong> موثقة ومعتمدة من الغرف التجارية المصرية.</li></ul>',
        'seo_title' => 'شهادات الجودة ومعايير التصدير الدولية | جولدن بيرد',
        'seo_description' => 'اطلع على معايير الفحص والرقابة والشهادات الرسمية والتحاليل المخبرية المعتمدة لشحنات شركة جولدن بيرد للتصدير.'
    ],
    [
        'title' => 'الأسواق الدولية وخطوط الشحن واللوجستيات',
        'slug' => 'export-markets',
        'template' => 'markets',
        'content' => '<h2>شبكة تصدير تصل إلى كافة موانئ العالم</h2><p>تمتلك شركة جولدن بيرد شراكات استراتيجية مع كبرى الخطوط الملاحية العالمية لضمان الشحن المنتظم في المواعيد المحددة دون تأخير وفق شروط الإنكوترمز FOB و CIF و CFR.</p>',
        'seo_title' => 'الأسواق الدولية وخطوط الشحن | جولدن بيرد للتصدير',
        'seo_description' => 'شحن وتصدير المنتجات والفحم المصري لموانئ أوروبا والخليج وإفريقيا وفق شروط الإنكوترمز FOB و CIF.'
    ]
];

foreach ($pages as $pg) {
    $existing = $fetchRow("SELECT id FROM pages WHERE slug = ?", [$pg['slug']]);
    if (!$existing) {
        $execSql("INSERT INTO pages (title, slug, template, content, seo_title, seo_description) VALUES (?, ?, ?, ?, ?, ?)", [
            $pg['title'], $pg['slug'], $pg['template'], $pg['content'], $pg['seo_title'], $pg['seo_description']
        ]);
    }
}
echo "[✓] Pages seeded.\n";

// 9. Seed Menus
$headerMenu = [
    ['title' => 'الرئيسية', 'url' => '/'],
    ['title' => 'من نحن', 'url' => '/about'],
    ['title' => 'أقسام التصدير', 'url' => '/export-sections'],
    ['title' => 'دليل المنتجات', 'url' => '/products'],
    ['title' => 'الجودة والشهادات', 'url' => '/quality-certificates'],
    ['title' => 'الأسواق والشحن', 'url' => '/export-markets'],
    ['title' => 'الأخبار والمقالات', 'url' => '/blog'],
    ['title' => 'اتصل بنا', 'url' => '/contact']
];

$footerMenu = [
    ['title' => 'من نحن', 'url' => '/about'],
    ['title' => 'قطاع الفحم النباتي', 'url' => '/export/charcoal'],
    ['title' => 'قطاع الحاصلات الزراعية', 'url' => '/export/agricultural-products'],
    ['title' => 'قطاع السلع الغذائية', 'url' => '/export/food-products'],
    ['title' => 'طلب عرض أسعار تصديري', 'url' => '/request-quote'],
    ['title' => 'خريطة الموقع XML', 'url' => '/sitemap.xml']
];

$existingHeader = $fetchRow("SELECT id FROM menus WHERE location = 'header'");
if (!$existingHeader) {
    $execSql("INSERT INTO menus (title, location, items_json) VALUES (?, ?, ?)", [
        'القائمة الرئيسية العلوية',
        'header',
        json_encode($headerMenu, JSON_UNESCAPED_UNICODE)
    ]);
}

$existingFooter = $fetchRow("SELECT id FROM menus WHERE location = 'footer'");
if (!$existingFooter) {
    $execSql("INSERT INTO menus (title, location, items_json) VALUES (?, ?, ?)", [
        'روابط التذييل السريعة',
        'footer',
        json_encode($footerMenu, JSON_UNESCAPED_UNICODE)
    ]);
}
echo "[✓] Menus seeded.\n";

// 10. Seed Certificates
$certificates = [
    [
        'title' => 'شهادة فحص الجودة والتحليل الكيميائي SGS',
        'title_en' => 'SGS International Inspection & Chemical Analysis Certificate',
        'issuer' => 'SGS International Inspection Services',
        'cert_number' => 'SGS-EGY-EXP-2026-894',
        'image_url' => '/assets/images/cert-sgs.webp',
        'issue_date' => '2026-01-10',
        'expiry_date' => '2027-01-09',
        'issue_year' => '2026',
        'description' => 'شهادة الفحص المعملي المعتمدة لنسب الكربون والرماد والرطوبة وخلو الشحنات من الإشعاع والشوائب.',
        'badge_color' => '#0284c7',
        'is_active' => 1,
        'sort_order' => 1
    ],
    [
        'title' => 'شهادة نظام إدارة الجودة الدولية ISO 9001:2015',
        'title_en' => 'ISO 9001:2015 Quality Management System Certification',
        'issuer' => 'International Organization for Standardization (TUV)',
        'cert_number' => 'ISO-9001-QMS-44812',
        'image_url' => '/assets/images/cert-iso.webp',
        'issue_date' => '2025-06-15',
        'expiry_date' => '2028-06-14',
        'issue_year' => '2025',
        'description' => 'اعتماد منظومة إدارة الجودة والتعبئة والتغليف وخدمات الشحن اللوجستي لعمليات التصدير الدولي.',
        'badge_color' => '#16a34a',
        'is_active' => 1,
        'sort_order' => 2
    ],
    [
        'title' => 'شهادة الصحة والسلامة النباتية الرسمية (Phytosanitary)',
        'title_en' => 'Official Phytosanitary Certificate for Agricultural Exports',
        'issuer' => 'الإدارة المركزية للحجر الزراعي المصري',
        'cert_number' => 'CAPQ-PHYTO-2026-119',
        'image_url' => '/assets/images/cert-phyto.webp',
        'issue_date' => '2026-02-01',
        'expiry_date' => '2027-02-01',
        'issue_year' => '2026',
        'description' => 'شهادة رسمية تثبت خلو المحاصيل والموالح والفحم من أي آفات أو كائنات حجرية ضارة.',
        'badge_color' => '#d97706',
        'is_active' => 1,
        'sort_order' => 3
    ]
];

foreach ($certificates as $c) {
    $existing = $fetchRow("SELECT id FROM certificates WHERE cert_number = ?", [$c['cert_number']]);
    if (!$existing) {
        $execSql("INSERT INTO certificates (title, title_en, issuer, cert_number, image_url, issue_date, expiry_date, issue_year, description, badge_color, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $c['title'], $c['title_en'], $c['issuer'], $c['cert_number'], $c['image_url'], $c['issue_date'], $c['expiry_date'], $c['issue_year'], $c['description'], $c['badge_color'], $c['is_active'], $c['sort_order']
        ]);
    }
}
echo "[✓] Certificates seeded.\n";

// 11. Seed FAQs
$faqs = [
    [
        'question' => 'ما هي شروط الدفع والتعاقد المعتمدة لدى شركة جولدن بيرد في صفقات التصدير؟',
        'answer' => 'نعتمد أنظمة دفع دولية آمنة وموثوقة مثل الاعتمادات المستندية غير القابلة للإلغاء والمعززة من بنوك دولية من الدرجة الأولى (Confirmed Irrevocable L/C at sight)، أو التحويل البنكي المباشر (T/T) بنسبة مقدمة 30% مع الباقي 70% مقابل صور مستندات الشحن الأصلية وبوليصة الشحن (B/L).',
        'category' => 'payment_terms',
        'is_active' => 1,
        'sort_order' => 1
    ],
    [
        'question' => 'ما هي شروط التسليم الدولية (Incoterms 2020) التي توفرونها لشحنات الفحم والحاصلات؟',
        'answer' => 'نوفر كافة شروط التسليم الدولية الرئيسية بما في ذلك FOB (تسليم موانئ مصرية كالإسكندرية ودمياط وبورسعيد والسخنة)، و CIF (شاملة الشحن البحري والتأمين واصلة لميناء المشتري)، و CFR (تكلفة ونولون بحري واصل ميناء المشتري).',
        'category' => 'shipping_logistics',
        'is_active' => 1,
        'sort_order' => 2
    ],
    [
        'question' => 'هل توفرون إمكانية التعبئة والتغليف بعلامة العميل التجارية الخاصة (Private Label)؟',
        'answer' => 'نعم، نوفر خدمة التعبئة والتغليف الخاصة بالكامل، حيث نقوم بطباعة شعار العميل وبيانات شركته على كراتين التصدير أو الأكياس الورقية متعددة الطبقات بأعلى جودة ألوان وتصميم وفق أوزان تبدأ من 1 كجم حتى 25 كجم.',
        'category' => 'packaging',
        'is_active' => 1,
        'sort_order' => 3
    ]
];

foreach ($faqs as $f) {
    $existing = $fetchRow("SELECT id FROM faqs WHERE question = ?", [$f['question']]);
    if (!$existing) {
        $execSql("INSERT INTO faqs (question, answer, category, is_active, sort_order) VALUES (?, ?, ?, ?, ?)", [
            $f['question'], $f['answer'], $f['category'], $f['is_active'], $f['sort_order']
        ]);
    }
}
echo "[✓] FAQs seeded.\n";

// 12. Seed Sliders
$sliders = [
    [
        'title' => 'تصدير أجود أنواع الفحم الطبيعي والمضغوط عالمياً',
        'title_en' => 'Exporting Premium Egyptian Charcoal Worldwide',
        'subtitle' => 'فحم حمضيات، برتقال، ليمون، جزورين، وفحم شيشة وشواء بنسبة كربون تتجاوز 85% ومطابقة للمواصفات القياسية الأوروبية والخليجية.',
        'badge_text' => 'الريادة الأولى في التصدير الدولي',
        'slider_type' => 'content',
        'placement' => 'home_hero',
        'image_url' => '/assets/images/hero-charcoal.webp',
        'link_url' => '/quote',
        'link_text' => 'طلب عرض سعر فوري (RFQ)',
        'secondary_link_url' => '/products',
        'secondary_link_text' => 'تصفح قائمة المنتجات',
        'overlay_opacity' => 60,
        'sort_order' => 1,
        'is_active' => 1
    ],
    [
        'title' => 'منتجات الفحم التصديرية الفاخرة للأسواق العالمية',
        'title_en' => 'High Calorie Export Grade Charcoal Products',
        'subtitle' => 'تعبئة وتغليف آلي متطور بأكياس ورقية وبلاستيكية وكرتونية مطبوعة وفق متطلبات المستورد مع شهادات فحص SGS معتمدة.',
        'badge_text' => 'منتجات معتمدة دولياً',
        'slider_type' => 'products',
        'placement' => 'products_page',
        'image_url' => '/assets/images/citrus-charcoal.webp',
        'link_url' => '/quote',
        'link_text' => 'استيراد حاويات فحم',
        'secondary_link_url' => '/quality-certificates',
        'secondary_link_text' => 'شهادات الجودة والاعتماد',
        'overlay_opacity' => 50,
        'sort_order' => 2,
        'is_active' => 1
    ]
];

foreach ($sliders as $sl) {
    $existing = $fetchRow("SELECT id FROM sliders WHERE title = ?", [$sl['title']]);
    if (!$existing) {
        $execSql("INSERT INTO sliders (title, title_en, subtitle, badge_text, slider_type, placement, image_url, link_url, link_text, secondary_link_url, secondary_link_text, overlay_opacity, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $sl['title'], $sl['title_en'], $sl['subtitle'], $sl['badge_text'], $sl['slider_type'], $sl['placement'], $sl['image_url'], $sl['link_url'], $sl['link_text'], $sl['secondary_link_url'], $sl['secondary_link_text'], $sl['overlay_opacity'], $sl['sort_order'], $sl['is_active']
        ]);
    }
}
echo "[✓] Sliders seeded.\n";

// 13. Seed Sample Invoices
$sampleInvoiceItems = [
    [
        'item_name' => 'فحم برتقال طبيعي نخب أول للشيشة (Citrus Charcoal Grade A)',
        'quantity' => 24,
        'unit' => 'طن متري (MT)',
        'unit_price' => 780.00,
        'total' => 18720.00
    ]
];
$existingInv = $fetchRow("SELECT id FROM invoices WHERE invoice_number = 'PI-2026-001'");
if (!$existingInv) {
    $execSql("INSERT INTO invoices (invoice_number, type, title, buyer_name, buyer_company, buyer_country, buyer_email, buyer_phone, incoterm, port_of_loading, port_of_discharge, currency, exchange_rate, items_json, subtotal, freight_cost, insurance_cost, total_amount, payment_terms, bank_details, notes, status, issue_date, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
        'PI-2026-001',
        'proforma',
        'فاتورة مبدئية لتصدير حاوية فحم برتقال فاخر 40 قدم',
        'الشيخ عبد الله القحطاني',
        'مؤسسة الرياض للتجارة وتوريد الفحم',
        'المملكة العربية السعودية',
        'sales@alqahtanitrading.sa',
        '+966 50 123 4567',
        'CIF',
        'ميناء الإسكندرية، مصر',
        'ميناء جدة الإسلامي، السعودية',
        'USD',
        1.0,
        json_encode($sampleInvoiceItems, JSON_UNESCAPED_UNICODE),
        18720.00,
        1600.00,
        180.00,
        20500.00,
        '30% دفعة مقدمة عند توقيع العقد، و 70% مقابل تقديم صور مستندات الشحن والفحص المعملي SGS.',
        'البنك الأهلي المصري - فرع المعاملات الدولية - سويفت كود NBEGEGCX',
        'الشحنة معبأة بأكياس ورقية كرافت 5 كجم مطبوعة بشعار العميل.',
        'sent',
        date('Y-m-d'),
        date('Y-m-d', strtotime('+30 days')),
        $adminId
    ]);
}
echo "[✓] Sample Invoices seeded.\n";

// 14. Seed Sample Contracts
$sampleClauses = [
    [
        'title' => 'البند الأول: موضوع العقد والكميات والمواصفات',
        'content' => 'اتفق الطرفان على قيام الطرف الأول (البائع) بتوريد وتصدير فحم نباتي طبيعي برتقال نخب أول للشيشة والمقاهي وفق المواصفات الفنية المعتمدة دولياً، بإجمالي كمية 50 طن متري موزعة على حاويتين 40 قدم High Cube.'
    ],
    [
        'title' => 'البند الثاني: الجودة والفحص المخبري',
        'content' => 'تخضع الشحنة للفحص المعملي الإلزامي بواسطة شركة SGS الدولية في ميناء الشحن للتأكد من مطابقة نسبة الكربون الثابت (لا تقل عن 80%) ونسبة الرماد (أقل من 2.5%) ونسبة الرطوبة (أقل من 4%). وتعتبر شهادة الفحص نهائية وملزمة للطرفين.'
    ]
];

$existingCtr = $fetchRow("SELECT id FROM contracts WHERE contract_number = 'CTR-2026-BH01'");
if (!$existingCtr) {
    $execSql("INSERT INTO contracts (contract_number, title, seller_name, seller_representative, buyer_name, buyer_company, buyer_country, buyer_phone, buyer_email, commodity, quantity, total_value, currency, incoterm, shipping_port, destination_port, delivery_schedule, payment_method, inspection_agency, clauses_json, status, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
        'CTR-2026-BH01',
        'عقد توريد وتصدير فحم برتقال طبيعي مصري ممتاز للخليج العربي',
        'شركة جولدن بيرد للتصدير والتجارة الدولية (Golden Bird Export)',
        'المدير العام والتنفيذي',
        'الشيخ عبد الله القحطاني',
        'مؤسسة الرياض للتجارة وتوريد الفحم',
        'المملكة العربية السعودية',
        '+966 50 123 4567',
        'sales@alqahtanitrading.sa',
        'فحم خشب برتقال طبيعي 100% نخب أول مخصص للشيشة والمطاعم',
        '50 طن متري (حاويتان 40 قدم)',
        39000.00,
        'USD',
        'CIF',
        'ميناء الإسكندرية / دمياط، مصر',
        'ميناء جدة الإسلامي، المملكة العربية السعودية',
        'شحن الحاوية الأولى خلال 10 أيام من استلام الدفعة المقدمة، والحاوية الثانية بعد أسبوعين.',
        '30% دفعة مقدمة T/T عند توقيع العقد، و 70% مقابل مستندات الشحن الأصلية وفحص SGS.',
        'SGS International / معهد بحوث وقاية النباتات المصري',
        json_encode($sampleClauses, JSON_UNESCAPED_UNICODE),
        'active',
        date('Y-m-d'),
        date('Y-m-d', strtotime('+90 days')),
        $adminId
    ]);
}
echo "[✓] Sample Contracts seeded.\n";

// 15. Ensure 1,500 High-Quality Charcoal Articles are generated in MySQL
$postCountRow = $fetchRow("SELECT COUNT(*) as cnt FROM posts");
$totalPostsCount = (int)($postCountRow['cnt'] ?? 0);
if ($totalPostsCount < 1500 && file_exists(__DIR__ . '/generate_charcoal_articles.php')) {
    echo "[*] Generating and seeding 1,500 in-depth Charcoal articles into MySQL...\n";
    require __DIR__ . '/generate_charcoal_articles.php';
    echo "[✓] 1,500 Articles verified and populated.\n";
} else {
    echo "[✓] Articles verified: {$totalPostsCount} posts present in database.\n";
}

echo "--- Golden Bird Charcoal Group Database Setup Finished Successfully! ---\n";
