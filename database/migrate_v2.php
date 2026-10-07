<?php
require_once __DIR__ . '/../app/bootstrap.php';
$db = \App\Core\Database::getInstance();

// 1. Create Certificates Table
$db->query("CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NULL,
    issuer VARCHAR(255) NOT NULL,
    cert_number VARCHAR(100) NULL,
    image VARCHAR(255) NULL,
    pdf_file VARCHAR(255) NULL,
    issue_date DATE NULL,
    expiry_date DATE NULL,
    description TEXT NULL,
    badge_color VARCHAR(50) DEFAULT '#10b981',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 2. Create FAQs Table
$db->query("CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL DEFAULT 'general',
    question VARCHAR(255) NOT NULL,
    question_en VARCHAR(255) NULL,
    answer TEXT NOT NULL,
    answer_en TEXT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 3. Seed initial certificates if empty
$certCount = $db->query("SELECT COUNT(*) as cnt FROM certificates")[0]['cnt'] ?? 0;
if ($certCount == 0) {
    $certs = [
        [
            'شهادة الجودة العالمية ISO 9001:2015',
            'ISO 9001:2015 Quality Management',
            'TUV Rheinland / IAF Global',
            'EG-QMS-2024-9812',
            'اعتماد نظام إدارة الجودة وتطبيق أعلى المعايير القياسية في فرز وتعبئة وتصدير الفحم والحاصلات الزراعية.',
            '#0284c7',
            1
        ],
        [
            'شهادة الممارسات الزراعية Global G.A.P',
            'Global G.A.P Good Agricultural Practices',
            'Control Union Certifications',
            'GGN-40598837199',
            'اعتماد الممارسات الزراعية السليمة وخلو الحاصلات من المتبقيات الكيميائية ومطابقة المعايير الأوروبية.',
            '#059669',
            2
        ],
        [
            'شهادة فحص وتفتيش الجودة SGS',
            'SGS Pre-Shipment Inspection Certificate',
            'SGS International Inspection',
            'SGS-EG-EXP-2024-441',
            'شهادة فحص الشحنات قبل الشحن لنسب الكربون، الرطوبة، الرماد، ونظافة الحاصلات من الشوائب والكسر.',
            '#d97706',
            3
        ],
        [
            'شهادة الصحة النباتية Phytosanitary Certificate',
            'Phytosanitary Export Certificate',
            'وزارة الزراعة والحجر الزراعي المصري',
            'PHYTO-EGY-2024-883',
            'شهادة رسمية معتمدة تثبت خلو جميع الشحنات الزراعية من أي آفات أو حشرات ومطابقتها للمواصفات الدولية.',
            '#10b981',
            4
        ],
        [
            'شهادة الحلال الدولية Halal Export Certificate',
            'Halal International Certification',
            'Halal Authority Global',
            'HAL-EGY-77312',
            'مطابقة الفحم النباتي والشيشة والحاصلات لكافة اشتراطات ومعايير الحلال المعمول بها دولياً.',
            '#78350f',
            5
        ]
    ];

    foreach ($certs as $c) {
        $db->query(
            "INSERT INTO certificates (title, title_en, issuer, cert_number, description, badge_color, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
            $c
        );
    }
}

// 4. Seed initial FAQs if empty
$faqCount = $db->query("SELECT COUNT(*) as cnt FROM faqs")[0]['cnt'] ?? 0;
if ($faqCount == 0) {
    $faqs = [
        [
            'incoterms',
            'ما هي شروط الشحن والتسليم الدولية (Incoterms) المتاحة لديكم؟',
            'ندعم الشحن وفق جميع شروط التجارة الدولية وأبرزها FOB (التسليم على ظهر السفينة في الموانئ المصرية كالإسكندرية ودمياط وبورسعيد والسخنة) و CIF (التسليم في ميناء المستورد شامل التأمين والشحن البحري) و CFR و EXW.'
        ],
        [
            'payment',
            'ما هي وسائل وشروط الدفع المعتمدة لدى مجموعة جولدن بيرد؟',
            'نقبل الاعتمادات المستندية غير القابلة للإلغاء والمعززة من بنوك دولية كبرى (100% Confirmed Irrevocable L/C at sight) وكذلك التحويلات البنكية المباشرة (T/T Advance Deposit + Balance against B/L copy).'
        ],
        [
            'charcoal',
            'ما هي مواصفات فحم الحمضيات والجزورين والشيشة المتاح للتصدير؟',
            'فحم نباتي طبيعي 100%، نسبة الكربون الثابت تتجاوز 78%-82%، نسبة الرماد أقل من 3% بلون أبيض ناصع، نسبة الرطوبة أقل من 5%، خالي تماماً من الشوائب والتراب والرائحة والدخان، ومدة اشتعال تدوم لأكثر من 4-5 ساعات متواصلة.'
        ],
        [
            'agro',
            'كيف تضمنون سلامة ونضارة الحاصلات الزراعية أثناء الشحن والتفريغ؟',
            'نستخدم أحدث الحاويات المبردة الذكية (Reefer Containers) المضبوطة بدقة على درجات حرارة ورطوبة محددة لكل منتج، مع فحص معملي دقيق وشهادة حجر زراعي معتمدة قبل إغلاق الحاوية بالشمع الجمركي.'
        ],
        [
            'packaging',
            'هل تقدمون خدمة التعبئة والتغليف بالعلامة التجارية الخاصة بنا (Private Label)؟',
            'نعم، نوفر خدمة التعبئة والتغليف المخصصة بالكامل (Private Labeling) بأوزان تبدأ من 1 كجم حتى 25 كجم في شكائر ورقية فاخرة، كراتين مطبوعة، أو أكياس بروبلين منسوجة حسب طلب المستورد والتصميم المعتمد لديه.'
        ]
    ];

    $order = 1;
    foreach ($faqs as $f) {
        $db->query(
            "INSERT INTO faqs (category, question, answer, sort_order, is_active) VALUES (?, ?, ?, ?, 1)",
            [$f[0], $f[1], $f[2], $order++]
        );
    }
}

// 5. Seed rich SEO settings if not exists
$seoKeys = [
    'seo_meta_title' => 'مجموعة جولدن بيرد لتصدير الفحم النباتي | تصدير الفحم والحاصلات الزراعية',
    'seo_meta_title_en' => 'Golden Bird Charcoal Export Group | Charcoal & Agricultural Commodities',
    'seo_meta_keywords' => 'تصدير فحم, فحم حمضيات, فحم جزورين, فحم شيشة, تصدير برتقال, تصدير بصل, تصدير ثوم, تصدير بطاطس, شحن بحري, FOB, CIF, مصر, الإسكندرية',
    'seo_meta_description' => 'شركة جولدن بيرد لتصدير الفحم النباتي - الرائدة في إنتاج وتصدير الفحم النباتي الطبيعي والحاصلات الزراعية المصرية عالية الجودة إلى كافة دول الخليج، أوروبا، وأفريقيا بأعلى المعايير الدولية.',
    'seo_og_image' => '/assets/images/og-share.jpg',
    'seo_google_verification' => 'google-site-verification=golden_bird_official_2026_verify',
    'seo_bing_verification' => 'bing-site-verification=golden_bird_bing_webmaster_code',
    'seo_google_analytics' => 'G-GOLDENBIRD2026',
    'seo_gtm_id' => 'GTM-GLDNBIRD',
    'seo_robots_txt' => "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /storage/\n\nSitemap: https://goldnberd.com/sitemap.xml",
    'seo_schema_type' => 'Corporation',
    'seo_structured_data_enabled' => '1',
    'seo_canonical_domain' => 'https://goldnberd.com'
];

foreach ($seoKeys as $k => $v) {
    $exists = $db->query("SELECT id FROM settings WHERE setting_key = ?", [$k]);
    if (empty($exists)) {
        $db->query("INSERT INTO settings (setting_key, setting_value, group_name, autoload) VALUES (?, ?, 'seo', 1)", [$k, $v]);
    }
}

echo "Database v2 migration completed successfully!\n";
