<?php
/**
 * Golden Bird for Charcoal & Export - Accurate Database Seeder
 * Pure MySQL / MariaDB (InnoDB, utf8mb4)
 */

require_once __DIR__ . '/../app/bootstrap.php';

if (getenv('ALLOW_DESTRUCTIVE_SEED') !== 'true') {
    fwrite(STDERR, "Refusing destructive seed. Set ALLOW_DESTRUCTIVE_SEED=true explicitly for a disposable database.\n");
    exit(1);
}

$db = \App\Core\Database::getInstance();
$conn = $db->getConnection();

if ($conn instanceof \PDO) {
    $pdo = $conn;
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
} else {
    $mysqli = $conn;
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0;");
    // Create PDO for easy prepared statement loops
    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=3306;dbname=goldenbird_export_db;charset=utf8mb4",
        "goldenbird_user",
        "goldenbird_pass_2026!",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
}

// Clear existing tables
$pdo->exec("DELETE FROM settings;");
$pdo->exec("DELETE FROM products;");
$pdo->exec("DELETE FROM export_sections;");
$pdo->exec("DELETE FROM sliders;");
$pdo->exec("DELETE FROM certificates;");
$pdo->exec("DELETE FROM faqs;");
$pdo->exec("DELETE FROM users;");
$pdo->exec("DELETE FROM roles;");
$pdo->exec("DELETE FROM contracts;");
$pdo->exec("DELETE FROM invoices;");

// 1. Roles
$roles = [
    [1, 'الإدارة العليا والتصدير (Super Admin)', 'super_admin', json_encode(['all' => true, 'sections' => true, 'products' => true, 'pages' => true, 'posts' => true, 'media' => true, 'quotes' => true, 'users' => true, 'settings' => true, 'logs' => true])],
    [2, 'إدارة المحتوى والترويج (Content Editor)', 'editor', json_encode(['sections' => true, 'products' => true, 'pages' => true, 'posts' => true, 'media' => true])],
    [3, 'مدير المبيعات الدولية (International Sales)', 'sales_manager', json_encode(['quotes' => true, 'contact' => true, 'products' => true])]
];
$roleStmt = $pdo->prepare("INSERT INTO roles (id, name, slug, permissions) VALUES (?, ?, ?, ?)");
foreach ($roles as $r) {
    $roleStmt->execute($r);
}

// 2. Admin User
$adminRawPassword = getenv('ADMIN_PASS') ?: '';
if (strlen($adminRawPassword) < 12) {
    fwrite(STDERR, "ADMIN_PASS must be set and contain at least 12 characters.\n");
    exit(1);
}
$adminPassword = password_hash($adminRawPassword, PASSWORD_BCRYPT, ['cost' => 12]);
$userStmt = $pdo->prepare("INSERT INTO users (id, role_id, name, email, password_hash, status) VALUES (?, ?, ?, ?, ?, ?)");
$userStmt->execute([1, 1, 'Golden Bird Executive Board', 'export@goldnberd.com', $adminPassword, 'active']);

// 3. Settings for Golden Bird Charcoal
$settings = [
    ['site_name', 'شركة جولدن بيرد لتصنيع وتصدير الفحم | Golden Bird Charcoal', 'general'],
    ['site_url', 'https://goldnberd.com', 'general'],
    ['seo_canonical_domain', 'https://goldnberd.com', 'seo'],
    ['site_tagline', 'الريادة العالمية في تصنيع وتصدير أجود أنواع الفحم النباتي الطبيعي والمضغوط للمشاوي والشيشة', 'general'],
    ['company_email', 'export@goldnberd.com', 'contact'],
    ['company_sales_email', 'sales@goldnberd.com', 'contact'],
    ['company_info_email', 'info@goldnberd.com', 'contact'],
    ['company_phone', '+20 100 025 1645', 'contact'],
    ['company_whatsapp', '01000251645', 'contact'],
    ['company_address', 'المصانع والمستودعات المركزية: المنطقة الصناعية الاستثمارية - ميناء دمياط / الإسكندرية - جمهورية مصر العربية', 'contact'],
    ['working_hours', 'السبت - الخميس: 8:00 صباحاً - 6:00 مساءً (توقيت القاهرة GMT+2) - قسم التصدير الدولي يعمل 24/7', 'contact'],
    ['export_license', 'سجل مصدرين معتمد رقم: 594218 / ترخيص هيئة الرقابة على الصادرات والواردات', 'general'],
    ['site_logo', '/assets/images/golden-bird-logo.svg', 'general'],
    ['site_favicon', '/assets/images/golden-bird-logo.svg', 'general'],
    ['site_operator_name', 'Golden Bird Industrial Group', 'general'],
    ['site_copyright_text', 'جميع الحقوق محفوظة لشركة جولدن بيرد لتصنيع وتصدير الفحم الطبيعي والمضغوط © ' . date('Y') . ' | Golden Bird for Charcoal & Export (goldnberd.com)', 'general'],
    ['facebook_url', 'https://facebook.com/goldenbirdcharcoal', 'social'],
    ['linkedin_url', 'https://linkedin.com/company/goldenbirdcharcoal', 'social'],
    ['instagram_url', 'https://instagram.com/goldenbirdcharcoal', 'social'],
    ['youtube_url', 'https://youtube.com/@goldenbirdcharcoal', 'social'],
    ['default_seo_title', 'Golden Bird Charcoal | مصنع ومصدر فحم الشيشة والباربكيو والمضغوط الأول عالمياً', 'seo'],
    ['default_seo_description', 'شركة جولدن بيرد لتصنيع وتصدير الفحم (goldnberd.com): إنتاج وتصدير فحم البرتقال، فحم الحمضيات، فحم المشاوي الصلب، فحم جوز الهند، والفحم المضغوط بمواصفات مخبرية دقيقة وكربون ثابت يتجاوز 82% وتسليم كبرى موانئ العالم.', 'seo'],
    ['default_seo_keywords', 'golden bird charcoal, goldnberd.com, تصدير فحم, مصنع فحم نباتي, فحم برتقال, فحم مشاوي, فحم شيشة, فحم جوز هند, فحم مضغوط سداسي, فحم طبيعي للشواء, استيراد فحم من مصر, charcoal factory egypt, shisha charcoal export, bbq hardwood charcoal, coconut briquettes supplier', 'seo'],
    ['google_analytics_id', '', 'seo'],
    ['header_notice', 'طاقة إنتاجية شهرية تتجاوز 1,800 طن متري | جاهزية تامة لتلبية تعاقدات التوريد السنوية وشحن الحاويات لكافة الموانئ العالمية وفق شروط FOB, CIF, CFR.', 'general'],
    ['monthly_production_capacity', '1,800 طن متري / شهر', 'specs'],
    ['fixed_carbon_guarantee', '80% - 86%', 'specs'],
    ['ash_content_guarantee', '1.8% - 3.2% (رماد أبيض نقي)', 'specs'],
    ['moisture_guarantee', '< 4.5%', 'specs'],
    ['burning_duration_guarantee', '4.5 - 6 ساعات متواصلة', 'specs'],
    ['export_destinations_count', '38+ دولة وميناء دولي', 'specs']
];
$settingStmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, group_name) VALUES (?, ?, ?)");
foreach ($settings as $s) {
    $settingStmt->execute($s);
}

// 4. Charcoal Sections
$sections = [
    [
        1,
        'فحم الشيشة والأراجيل الفاخر',
        'shisha-charcoal',
        'فحم طبيعي ومضغوط نخب أول مصمم للمقاهي واللاونجات الراقية، برائحة محايدة وحرارة متجانسة ورماد أبيض ناصع.',
        '<p>تنتج <strong>جولدن بيرد</strong> أرقى درجات فحم الشيشة المستخرج من خشب أشجار الحمضيات والبرتقال المعمرة، بالإضافة إلى مكعبات فحم جوز الهند الصافي 100%. يتميز هذا الفحم بخلوه التام من المواد الكيميائية، ولا يطلق أي شرر أو أدخنة مزعجة، مما يحافظ على النكهة النقية للمعسل ويستمر في الاشتعال لأكثر من 5 ساعات متواصلة برماد متماسك لا يتطاير.</p>',
        'flame',
        1,
        'published',
        'فحم الشيشة الطبيعي الفاخر | تصدير فحم الأراجيل نخب أول - Golden Bird',
        'مصنع ومصدر فحم الشيشة عالي الجودة: فحم برتقال، فحم حمضيات، ومكعبات جوز هند بأقل نسبة رماد وحرارة مستمرة لأكثر من 5 ساعات.'
    ],
    [
        2,
        'فحم المشاوي والباربكيو الاحترافي (BBQ)',
        'bbq-charcoal',
        'فحم أخشاب صلبة طبيعية (Hardwood) مخصص للمطاعم ومطابخ الشواء الاحترافية ومحبي الباربكيو، بحرارة عالية وطاقة كربونية مركزة.',
        '<p>ننتج فحم المشاوي والباربكيو من أخشاب الغابات الصلبة مثل الجزورين، الطلح، والزان المعالج حرارياً عبر مكامير متطورة بيئياً. يتم فرز الأحجام بعناية (غربلة من 4 سم حتى 20 سم) لضمان اشتعال سهل، وطاقة حرارية هائلة تمنح اللحوم نكهة الشواء الأصيلة دون انبعاث روائح ضارة أو دخان خانق.</p>',
        'fire',
        2,
        'published',
        'فحم المشاوي والباربكيو الاحترافي Hardwood BBQ Charcoal | Golden Bird',
        'تصدير فحم المشاوي للمطاعم والفنادق وسلاسل التجزئة: أحجام مدروسة، طاقة حرارية جبارة، اشتعال طويل، وتعبئة حسب طلب المستورد.'
    ],
    [
        3,
        'الفحم المضغوط وقوالب الباركيت عالية الكثافة',
        'briquettes-charcoal',
        'قوالب فحم مضغوطة أوتوماتيكياً (سداسية مع تجويف حراري ومكعبات) بكثافة صلبة واحتراق منتظم واقتصادي فائق الكفاءة.',
        '<p>تعتمد خطوط إنتاج <strong>Golden Bird</strong> على أحدث المكابس الهيدروليكية لتحويل مسحوق الفحم العضوي النقي وغبار الأخشاب الصلبة الطبيعية وقشور جوز الهند إلى قوالب سداسية ومكعبات مدمجة بدون أي مواد رابطة كيماوية. تضمن هذه القوالب أعلى كفاءة احتراق متواصلة تصل إلى 6 ساعات مع حرارة مركزة للغاية ورماد قليل جداً.</p>',
        'layers',
        3,
        'published',
        'الفحم المضغوط وقوالب الباركيت السداسية والمكعبات | Golden Bird Charcoal',
        'إنتاج وتصدير الفحم المضغوط السداسي مع الفجوة الهوائية ومكعبات الشيشة والباربكيو بجودة أوروبية قياسية وأسعار جملة منافسة.'
    ],
    [
        4,
        'حلول التعبئة والتصنيع للغير (Private Label)',
        'packaging-private-label',
        'خدمات التعبئة المخصصة والتصميم والطباعة لحساب العلامات التجارية التابعة للمستوردين وسلاسل الهايبرماركت العالمية.',
        '<p>نوفر لعملائنا حول العالم خطوط تعبئة وتغليف مؤتمتة تدعم كافة خيارات التعبئة التصديرية: أكياس كرافت ثلاثية الطبقات مقاومة للرطوبة، كراتين مطبوعة بطلاء لامع مع أكياس داخلية معزولة، وأكياس خيش وبولي بروبيلين من 1 كجم إلى 25 كجم مع طباعة شعار وبيانات المستورد بجميع اللغات.</p>',
        'package',
        4,
        'published',
        'حلول التعبئة الخاصة والتصنيع لحساب الغير Private Label Charcoal | Golden Bird',
        'خدمات التعبئة والتغليف الخاصة بعلامتك التجارية: كراتين ملونة، أكياس كرافت، وأوزان متعددة مع الالتزام بالمواصفات القياسية الدولية.'
    ]
];

$secStmt = $pdo->prepare("INSERT INTO export_sections (id, name, slug, short_description, full_description, icon_code, sort_order, status, seo_title, seo_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($sections as $sec) {
    $secStmt->execute($sec);
}

// 5. Products
$products = [
    [
        1,
        1,
        'فحم خشب البرتقال النخب الأول (Super Citrus Shisha Charcoal)',
        'premium-orange-citrus-shisha-charcoal',
        'GBC-SH-01',
        'أرقى أنواع فحم الحمضيات الطبيعي المستخرج من أشجار البرتقال المعمرة، مخصص للشيشة الفاخرة برائحة طبيعية ورماد أبيض متماسك.',
        '<h3>المواصفات القياسية لفحم خشب البرتقال النخب الأول من Golden Bird</h3>
        <p>يعد فحم البرتقال المصري النخب الأول أحد أكثر أنواع الفحم طلباً في أسواق الخليج العربي وأوروبا، نظراً لخصائصه الفريدة الناتجة عن الكربنة البطيئة لأخشاب أشجار الموالح الصلبة في أفران مغلقة صديقة للبيئة تضمن أقصى درجات النقاء الكربوني.</p>',
        json_encode([
            'الكربون الثابت (Fixed Carbon)' => '83.5%',
            'نسبة الرطوبة (Moisture)' => '3.8%',
            'نسبة الرماد (Ash Content)' => '1.9%',
            'لون الرماد' => 'أبيض فضي ناصع (Pure White)',
            'القيمة الحرارية (Calorific Value)' => '7850 kcal/kg',
            'مدة الاشتعال' => '5.0 - 5.5 ساعات',
            'مستوى الشرر والدخان' => '0% خالٍ تماماً (Spark-Free & Smokeless)',
            'حجم القطع' => 'أصابع وألواح متجانسة 6 - 18 سم'
        ]),
        'أكياس كرافت 1-10 كجم / كراتين 10 كجم / خياش 15-20 كجم',
        'جمهورية مصر العربية',
        1,
        1,
        'published',
        'فحم برتقال مصري نخب أول لتصدير الشيشة | Golden Bird Charcoal',
        'استيراد فحم برتقال وحمضيات نخب أول للشيشة واللاونجات: كربون ثابت 84%، رماد أبيض ناصع، خلو تام من الشرر، وتسليم FOB/CIF لكافة الموانئ.'
    ],
    [
        2,
        1,
        'فحم الليمون الطبيعي فائق الصلابة (Natural Lemon Wood Charcoal)',
        'natural-lemon-wood-charcoal-shisha',
        'GBC-SH-02',
        'فحم أشجار الليمون شديد الصلابة والكثافة، يتميز بحرارة ناعمة متوازنة وثبات كربوني يمنع حرق المعسل ويدوم لساعات طويلة.',
        '<h3>فحم خشب الليمون للتصدير الدولي من Golden Bird</h3>
        <p>يتميز خشب الليمون بأليافه الكثيفة والمتراصة، مما ينتج عنه فحم ذو كثافة نوعية مرتفعة للغاية وصوت زجاجي رنان عند الطرق. تم اختباره واعتماده لدى كبرى صالات ولاونجات الشيشة في المملكة العربية السعودية، الإمارات، ألمانيا والمملكة المتحدة.</p>',
        json_encode([
            'الكربون الثابت' => '82.0%',
            'الرطوبة' => '3.5%',
            'الرماد' => '2.1%',
            'لون الرماد' => 'رماد أبيض متماسك',
            'القيمة الحرارية' => '7700 kcal/kg',
            'مدة الاشتعال' => '4.5 - 5.0 ساعات',
            'الرائحة' => 'محايدة وطبيعية 100%'
        ]),
        'كراتين مخصصة 10 كجم / أكياس بولي بروبيلين منسوجة',
        'جمهورية مصر العربية',
        1,
        2,
        'published',
        'فحم ليمون طبيعي للشيشة عالي الجودة | مصنع Golden Bird',
        'تصدير فحم خشب الليمون الطبيعي: كثافة فائقة، احتراق بطيء، حرارة متزنة ورماد متماسك مع خيارات الشحن البحري السريع.'
    ],
    [
        3,
        2,
        'فحم الجزورين والأخشاب الصلبة للمشاوي والباربكيو (Heavy Hardwood BBQ Charcoal)',
        'hardwood-bbq-restaurant-charcoal',
        'GBC-BBQ-01',
        'فحم خشب الجزورين الطبيعي المخصص للمطاعم ومحترفي الشواء، يعطي جمر ملتهب وطاقة حرارية تتجاوز 8200 كالوري لاحتراق يدوم طويلاً.',
        '<h3>فحم المشاوي الاحترافي للمطاعم والمستوردين - Golden Bird Charcoal</h3>
        <p>صُمم هذا الصنف خصيصاً لتلبية متطلبات مطاعم المشويات الفاخرة (Steak Houses & BBQ Chains) ومطابخ الفنادق. يعتمد على أخشاب الجزورين المعمرة التي تتميز بمقاومة الاحتراق السريع وتوليد حرارة إشعاعية عالية ومستمرة.</p>',
        json_encode([
            'القيمة الحرارية' => '8200 kcal/kg',
            'الكربون الثابت' => '80.5%',
            'الرماد' => '2.8%',
            'الرطوبة' => '4.2%',
            'مدة الاشتعال' => '5.0 - 6.0 ساعات',
            'حجم القطع (Lump)' => 'قطع كبيرة 5 - 22 سم مغربلة'
        ]),
        'أكياس خيش طبيعي 15-20 كجم / أكياس بولي بروبيلين 10-25 كجم',
        'جمهورية مصر العربية',
        1,
        3,
        'published',
        'فحم مشاوي خشب صلب للمطاعم Hardwood BBQ | Golden Bird Charcoal',
        'توريد وتصدير فحم المشاوي والباربكيو الطبيعي من خشب الجزورين الصلب: قطع كبيرة متجانسة، حرارة بركانية مستمرة وأسعار جملة منافسة.'
    ],
    [
        4,
        2,
        'فحم الطلح الإفريقي البري للشواء الثقيل (Wild Acacia Charcoal)',
        'wild-acacia-hardwood-charcoal',
        'GBC-BBQ-02',
        'فحم مستخرج من أخشاب شجر الطلح الطبيعي، يتميز بكثافة ألياف أسطورية واشتعال هادئ وقوة حرارية جبارة تناسب الشواء في الهواء الطلق.',
        '<h3>فحم الطلح الطبيعي النخب الأول</h3>
        <p>يعد فحم الطلح من أقوى أنواع الفحم الطبيعي في العالم بفضل صلابة بنيته الخشبية. مفضل لدى عشاق التخييم، المطاعم الكبرى، وحفلات الباربكيو الطويلة.</p>',
        json_encode([
            'الكربون الثابت' => '82.0%',
            'القيمة الحرارية' => '8050 kcal/kg',
            'الرطوبة' => '3.9%',
            'الرماد' => '2.4%',
            'مدة التوهج' => '5.5 - 6.5 ساعات'
        ]),
        'أكياس خيش 20 كجم / أكياس كرافت مقواة',
        'جمهورية مصر العربية / السودان',
        1,
        4,
        'published',
        'فحم طلح طبيعي للشواء والباربكيو | Golden Bird Export',
        'تصدير فحم أخشاب الطلح الطبيعي فائق الصلابة بحرارة عالية واستدامة اشتعال ممتازة للمستوردين حول العالم.'
    ],
    [
        5,
        3,
        'فحم قوالب جوز الهند المكعبة للشيشة (100% Pure Coconut Charcoal Cubes)',
        'pure-coconut-charcoal-briquettes-cubes',
        'GBC-COC-01',
        'مكعبات فحم قشور جوز الهند الطبيعية 100% بدون أي مواد رابطة كيماوية، رماد ناصع البياض لا يتجاوز 2% واشتعال يدوم لساعتين ونصف لكل مكعب.',
        '<h3>مكعبات فحم جوز الهند الفاخرة للشيشة من Golden Bird</h3>
        <p>يتم تصنيع هذه المكعبات من أنقى قشور جوز الهند العضوية المعالجة بالكربنة الدقيقة. نستخدم ضغطاً هيدروليكياً فائقاً مع النشا الطبيعي النباتي كمادة رابطة عضوية، دون أي كبريت أو مواد بترولية أو كيميائية ضارة.</p>',
        json_encode([
            'المقاس القياسي' => '25x25x25 مم و 26x26x26 مم',
            'الكربون الثابت' => '84.5%',
            'نسبة الرماد' => '1.8% (Pure Snow White)',
            'الرطوبة' => '3.2%',
            'زمن الاشتعال للمكعب' => '120 - 150 دقيقة',
            'المواد الكيميائية' => '0% عضوي بالكامل'
        ]),
        'كرتونة 1 كجم مطبوعة فاخرة (Master Carton 10x1kg أو 20x1kg)',
        'جمهورية مصر العربية / إندونيسيا',
        1,
        5,
        'published',
        'مكعبات فحم جوز الهند للشيشة 100% طبيعي | Golden Bird',
        'مصنع ومصدر مكعبات فحم جوز الهند الطبيعي للشيشة: مقاسات 25x25 و 26x26، رماد أبيض نقي واشتعال متواصل بدون رائحة أو دخان.'
    ],
    [
        6,
        3,
        'الفحم المضغوط السداسي ذو التجويف الحراري (Hexagonal Sawdust Charcoal Briquettes)',
        'hexagonal-sawdust-briquettes-charcoal',
        'GBC-HEX-01',
        'قوالب فحم أخشاب صلبة مضغوطة بالشكل السداسي مع الفجوة الهوائية المركزية، مصممة للمطاعم اليابانية والكورية والشواء الطويل بدون دخان.',
        '<h3>فحم الأخشاب المضغوط السداسي ذو الكفاءة الحرارية القصوى</h3>
        <p>يمثل الفحم السداسي المضغوط قمة التكنولوجيا التصديرية الحديثة في معالجة غبار الخشب الصلب الطبيعي (Sawdust). تعمل الفجوة الأسطوانية في قلب كل قالب كمدخنة حرارية تسحب تيار الهواء وتضمن توهجاً نارياً شديداً ومنتظماً من الداخل والخارج دون إطلاق أي ألسنة لهب أو دخان.</p>',
        json_encode([
            'الكثافة النوعية' => '1.3 جم/سم³ (صلابة فائقة)',
            'الكربون الثابت' => '86.0%',
            'القيمة الحرارية' => '8100 kcal/kg',
            'الرماد' => '2.2%',
            'الرطوبة' => '2.8%',
            'مدة الاشتعال' => '5.5 - 6.5 ساعات',
            'الشكل الهندسي' => 'سداسي مفرغ طول 10 - 35 سم'
        ]),
        'كراتين مقواة 10 كجم مع أكياس عزل',
        'جمهورية مصر العربية',
        1,
        6,
        'published',
        'الفحم المضغوط السداسي عالي الكثافة Sawdust Charcoal | Golden Bird',
        'تصدير فحم القوالب السداسية المضغوطة للمطاعم والشواء الطويل: كثافة فائقة، حرارة بركانية، وخلو تام من الدخان والرماد المتطاير.'
    ]
];

$prodStmt = $pdo->prepare("INSERT INTO products (
    id, export_section_id, name, slug, sku, short_description, full_description,
    specifications, packaging_options, origin_country,
    is_featured, sort_order, status, seo_title, seo_description
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($products as $p) {
    $prodStmt->execute($p);
}

// 6. Sliders
$sliders = [
    [
        'الريادة العالمية في صناعة وتصدير الفحم النباتي',
        'Golden Bird Charcoal • Global Export Standard',
        'فحم طبيعي نخب أول ومكعبات جوز هند وقوالب مضغوطة بمواصفات مخبرية قياسية وشحن بحري مباشر لكافة موانئ العالم.',
        'Golden Bird Charcoal • Global Export',
        'charcoal',
        'home_hero',
        '/assets/images/slider-charcoal-production.svg',
        '/request-quote',
        'طلب عرض أسعار تصديري (RFQ)',
        1,
        1
    ],
    [
        'فحم خشب البرتقال الفاخر للشيشة واللاونجات الراقية',
        'Super Citrus Shisha Charcoal',
        'كربون ثابت يتجاوز 84%، رماد أبيض ناصع، خلو تام من الشرر والدخان، واشتعال متواصل لأكثر من 5 ساعات دون تغيير نكهة المعسل.',
        'Premium Citrus Shisha Charcoal',
        'charcoal',
        'home_hero',
        '/assets/images/slider-citrus-charcoal.svg',
        '/product/premium-orange-citrus-shisha-charcoal',
        'استكشف مواصفات فحم البرتقال',
        2,
        1
    ],
    [
        'حلول التعبئة والتصنيع لحساب الغير (Private Labeling)',
        'Private Label & Custom Packaging Studio',
        'نوفر لشركائنا الدوليين تصاميم كراتين وأكياس كرافت مخصصة بعلامتهم التجارية مع طباعة متعددة اللغات ومطابقة للمواصفات الجمركية.',
        'Private Label & Custom Packaging',
        'charcoal',
        'home_hero',
        '/assets/images/slider-private-label.svg',
        '/packaging-options',
        'خيارات التعبئة والكراتين المخصصة',
        3,
        1
    ]
];

$sliderStmt = $pdo->prepare("INSERT INTO sliders (title, title_en, subtitle, badge_text, slider_type, placement, image_url, link_url, link_text, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($sliders as $sl) {
    $sliderStmt->execute($sl);
}

// 7. Certificates
$certificates = [
    [
        'شهادة الفحص المخبري والتحليل الكربوني من SGS الدولية',
        'SGS International Laboratory Quality & Carbon Analysis Report',
        'SGS Société Générale de Surveillance S.A.',
        'SGS-EG-CHC-89421',
        '/public/assets/images/cert-sgs.svg',
        '2025-01-15',
        'توثق الفحوصات المعملية خلو الشحنات من المواد المشعة والكيماوية، ومطابقة نسب الكربون الثابت (>83%) والرماد (<2.5%) للمواصفات القياسية الأوروبية.',
        1,
        1
    ],
    [
        'شهادة المطابقة لسلامة النقل البحري للبضائع غير الخطرة (Non-DG / MSDS)',
        'Material Safety Data Sheet (MSDS) & Non-Hazardous Cargo Approval',
        'Bureau Veritas / International Maritime Safety',
        'BV-NON-DG-2025-004',
        '/public/assets/images/cert-msds.svg',
        '2025-02-01',
        'شهادة رسمية تثبت خضوع الفحم لمعاملات التبريد والتهوية الكافية، مما يجعله آمناً للنقل في الحاويات البحرية القياسية كبضاعة غير خطرة وغير قابلة للاشتعال الذاتي.',
        1,
        2
    ],
    [
        'شهادة الأيزو العالمية لإدارة الجودة ISO 9001:2015',
        'ISO 9001:2015 Quality Management Systems Certification',
        'TÜV Rheinland International',
        'TUV-ISO-9001-GB7721',
        '/public/assets/images/cert-iso.svg',
        '2024-11-20',
        'اعتماد معايير الجودة الشاملة في كافة مراحل الإنتاج، من انتقاء أخشاب الأشجار، والتحكم في المكامير، والغربلة، وحتى التعبئة والشحن.',
        1,
        3
    ]
];

$certStmt = $pdo->prepare("INSERT INTO certificates (title, title_en, issuer, cert_number, image_url, issue_date, description, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($certificates as $c) {
    $certStmt->execute($c);
}

// 8. FAQs
$faqs = [
    [
        'ما هي الطاقة الإنتاجية الشهرية لمصانع Golden Bird Charcoal؟',
        'What is the monthly production capacity of Golden Bird Charcoal factories?',
        'تتجاوز طاقتنا الإنتاجية الإجمالية 1,800 طن متري شهرياً موزعة بين فحم الحمضيات الطبيعي للشيشة، فحم المشاوي الصلب، ومكعبات وقوالب الفحم المضغوط، مما يضمن تلبية العقود السنوية وتجهيز حاويات أسبوعية دون أي تأخير.',
        'Our total production capacity exceeds 1,800 metric tons per month across natural citrus shisha charcoal, hardwood BBQ charcoal, and compressed coconut briquettes.',
        'production',
        1,
        1
    ],
    [
        'ما هي شروط التجارة الدولية (Incoterms) وطرق الدفع المعتمدة لديكم؟',
        'What Incoterms and international payment methods do you accept?',
        'نقدم أسعاراً رسمية بشروط FOB (التسليم على ظهر السفينة بموانئ دمياط، الإسكندرية، وبورسعيد)، وشروط CIF / CFR (شاملاً الشحن والتأمين حتى ميناء الوصول للمستورد). نقبل الدفع عبر الاعتمادات المستندية المعززة غير القابلة للإلغاء (Irrevocable Confirmed L/C at sight) أو التحويلات البنكية المباشرة (T/T).',
        'We offer FOB, CIF, and CFR terms. We accept Irrevocable Confirmed L/C at sight and Telegraphic Transfer (T/T).',
        'payment_shipping',
        1,
        2
    ],
    [
        'هل توفرون خدمة التعبئة والتغليف الخاصة باسم وعلامة المستورد (Private Label)؟',
        'Do you provide Private Labeling and customized packaging for buyers?',
        'نعم بكل تأكيد، نوفر استوديو تصميم وطباعة متكامل لتصنيع وتعبئة كراتين وأكياس كرافت وأكياس بولي بروبيلين تحمل الشعار والبيانات والباركود الخاص بشركتكم بجميع اللغات والمقاسات من 1 كجم حتى 25 كجم.',
        'Yes, we provide full private labeling services including design, printing of kraft bags, carton boxes, and PP woven bags with your custom brand logos.',
        'packaging',
        1,
        3
    ],
    [
        'كم تبلغ حمولة الحاوية 40 قدم High Cube من الفحم الطبيعي مقابل الفحم المضغوط؟',
        'How many tons can be loaded into a 40ft HC container for natural vs briquettes charcoal?',
        'تتسع حاوية 40ft HC عادةً لـ 18 إلى 22 طناً مترياً من فحم البرتقال والحمضيات الطبيعي المفروز والمعبأ بكراتين، بينما تتسع لـ 26 إلى 28 طناً مترياً من الفحم المضغوط ومكعبات جوز الهند بفضل كثافتها العالية وترتيبها الهندسي داخل الكراتين.',
        'A 40ft HC container typically holds 18-22 metric tons of natural lump citrus charcoal, and 26-28 metric tons of compressed sawdust or coconut briquettes.',
        'shipping',
        1,
        4
    ],
    [
        'كيف تضمنون خلو شحنات الفحم من الشرر والدخان والروائح المزعجة؟',
        'How do you guarantee spark-free, odorless, and smokeless charcoal shipments?',
        'تخضع جميع خطوط الكربنة لنظام رقابة حراري إلكتروني يضمن اكتمال التحلل الحراري للمواد العضوية المتطايرة (Volatile Matter < 10%)، مع فحص مخبري لكل دفعة قبل التعبئة واستبعاد أي أخشاب غير تامة التفحم أو ذات رطوبة مرتفعة.',
        'All carbonization batches undergo strict retort thermal regulation ensuring complete pyrolysis, keeping volatile matter under 10% for a spark-free and smokeless experience.',
        'quality',
        1,
        5
    ]
];

$faqStmt = $pdo->prepare("INSERT INTO faqs (question, question_en, answer, answer_en, category, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($faqs as $f) {
    $faqStmt->execute($f);
}

// 9. Sample Initial Invoice & Contract for Golden Bird
$invoiceItems = [
    [
        'description' => 'Super Citrus Shisha Charcoal - Grade A (Orange Wood)',
        'quantity' => 22,
        'unit' => 'Metric Ton',
        'unit_price' => 780.00,
        'total' => 17160.00
    ],
    [
        'description' => 'Custom 10kg Master Cartons with Private Labeling & Polybag Lining',
        'quantity' => 2200,
        'unit' => 'Boxes',
        'unit_price' => 0.85,
        'total' => 1870.00
    ]
];

$invStmt = $pdo->prepare("INSERT INTO invoices (
    invoice_number, type, title, buyer_name, buyer_company, buyer_country,
    buyer_email, buyer_phone, incoterm, port_of_loading, port_of_discharge,
    currency, exchange_rate, items_json, subtotal, freight_cost, total_amount,
    payment_terms, bank_details, notes, template, status, issue_date, due_date
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$invStmt->execute([
    'INV-GBC-2026-001',
    'proforma',
    'Proforma Invoice - 1x40ft HC Premium Shisha Charcoal Shipment',
    'Mr. Alexander Weber',
    'EuroCharcoal Trade GmbH',
    'Germany',
    'weber@eurocharcoal-trade.de',
    '+49 40 8829100',
    'CIF',
    'Damietta Port / Alexandria, Egypt',
    'Port of Hamburg, Germany',
    'USD',
    1.0,
    json_encode($invoiceItems),
    19030.00,
    2400.00,
    21430.00,
    '30% Advance Deposit upon contract signing, 70% against Copy of Original Shipping Documents & Bill of Lading (B/L)',
    'Bank: National Bank of Egypt (NBE) / Beneficiary: Golden Bird for Charcoal & Export / IBAN: EG820002000100000019284729182 / SWIFT: NBEGEGCX',
    'Goods are 100% natural, spark-free, moisture below 4%, with SGS pre-shipment quality certificate included.',
    'classic_gold',
    'confirmed',
    date('Y-m-d'),
    date('Y-m-d', strtotime('+30 days'))
]);

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "Golden Bird Charcoal Database Seeded Successfully!\n";
