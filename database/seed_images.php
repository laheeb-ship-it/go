<?php
/**
 * Visual Assets Seeder for Golden Bird Charcoal CMS
 * Creates high-quality SVG visuals for products, export sectors, posts, and logo,
 * registers them in the media table, and links them to products, sections, and posts.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();
$imgDir = __DIR__ . '/../public/assets/images';
if (!is_dir($imgDir)) {
    mkdir($imgDir, 0755, true);
}

// 1. Define Visual SVG Assets
$assets = [
    'logo' => [
        'file' => 'golden-bird-logo.svg',
        'alt' => 'شعار شركة جولدن بيرد لتصدير الفحم النباتي',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 450 110" width="100%" height="100%">
            <defs>
                <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#F59E0B" />
                    <stop offset="50%" stop-color="#FDE68A" />
                    <stop offset="100%" stop-color="#D97706" />
                </linearGradient>
                <linearGradient id="navyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#0F1E36" />
                    <stop offset="100%" stop-color="#070E1A" />
                </linearGradient>
            </defs>
            <g transform="translate(10, 10)">
                <rect width="90" height="90" rx="16" fill="url(#navyGrad)" stroke="url(#goldGrad)" stroke-width="2" />
                <path d="M45 20 L68 35 L68 65 L45 80 L22 65 L22 35 Z" fill="none" stroke="url(#goldGrad)" stroke-width="3" />
                <path d="M45 30 L58 40 L58 60 L45 70 L32 60 L32 40 Z" fill="url(#goldGrad)" opacity="0.25" />
                <text x="45" y="58" font-family="Cairo, Arial, sans-serif" font-size="34" font-weight="900" fill="url(#goldGrad)" text-anchor="middle">🦅</text>
            </g>
            <text x="120" y="48" font-family="Cairo, Arial, sans-serif" font-size="26" font-weight="900" fill="#0F1E36">شركة جولدن بيرد لتصدير الفحم</text>
            <text x="120" y="74" font-family="Plus Jakarta Sans, Arial, sans-serif" font-size="13" font-weight="800" letter-spacing="2" fill="#D97706">GOLDEN BIRD CHARCOAL EXPORT</text>
            <text x="120" y="94" font-family="Cairo, Arial, sans-serif" font-size="11" fill="#64748B">الريادة الدولية في تصدير الفحم النباتي الطبيعي والصناعي</text>
        </svg>'
    ],
    'sec_charcoal' => [
        'file' => 'sector-charcoal.svg',
        'alt' => 'قطاع الفحم النباتي للشواء والشيشة',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 400" width="100%" height="100%">
            <defs>
                <linearGradient id="charBg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1E293B" />
                    <stop offset="100%" stop-color="#0F172A" />
                </linearGradient>
                <linearGradient id="ember" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#FF4500" />
                    <stop offset="50%" stop-color="#FFA500" />
                    <stop offset="100%" stop-color="#FFD700" />
                </linearGradient>
            </defs>
            <rect width="600" height="400" fill="url(#charBg)" />
            <circle cx="300" cy="200" r="140" fill="#334155" opacity="0.3" />
            <path d="M220 280 Q300 120 380 280 Z" fill="#000000" opacity="0.6" />
            <!-- Charcoal lumps -->
            <polygon points="180,290 230,220 280,240 260,300 200,310" fill="#18181B" stroke="#D4AF37" stroke-width="2" />
            <polygon points="270,290 320,190 380,210 390,290 330,310" fill="#27272A" stroke="#FF4500" stroke-width="2" />
            <polygon points="370,290 410,230 460,250 440,300 390,305" fill="#18181B" stroke="#D4AF37" stroke-width="2" />
            <!-- Glowing Embers -->
            <circle cx="300" cy="240" r="6" fill="url(#ember)" />
            <circle cx="340" cy="230" r="4" fill="url(#ember)" />
            <circle cx="320" cy="260" r="5" fill="url(#ember)" />
            <circle cx="250" cy="260" r="4" fill="url(#ember)" />
            <text x="300" y="80" font-family="Tajawal, sans-serif" font-size="28" font-weight="900" fill="#FFFFFF" text-anchor="middle">قطاع الفحم النباتي عالي النقاوة</text>
            <text x="300" y="115" font-family="Tajawal, sans-serif" font-size="16" fill="#D4AF37" text-anchor="middle">فحم برتقال • فحم كازوارينا • فحم جزورين • فحم مشاوي وشيشة</text>
            <rect x="200" y="340" width="200" height="35" rx="8" fill="#D4AF37" />
            <text x="300" y="363" font-family="Tajawal, sans-serif" font-size="14" font-weight="700" fill="#0F172A" text-anchor="middle">كربون ثابت > 78% • رماد أبيض</text>
        </svg>'
    ],
    'sec_agri' => [
        'file' => 'sector-agriculture.svg',
        'alt' => 'قطاع الحاصلات الزراعية والموالح',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 400" width="100%" height="100%">
            <defs>
                <linearGradient id="agriBg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#14532D" />
                    <stop offset="100%" stop-color="#052E16" />
                </linearGradient>
            </defs>
            <rect width="600" height="400" fill="url(#agriBg)" />
            <circle cx="300" cy="210" r="120" fill="#166534" opacity="0.5" />
            <!-- Orange & Citrus graphics -->
            <circle cx="250" cy="220" r="65" fill="#EA580C" stroke="#FDBA74" stroke-width="4" />
            <circle cx="230" cy="200" r="10" fill="#FED7AA" opacity="0.6" />
            <!-- Fresh Garlic graphics -->
            <path d="M350 260 C320 260 310 210 340 180 C360 160 380 180 390 210 C400 240 380 260 350 260 Z" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="3" />
            <path d="M345 180 L345 150" stroke="#15803D" stroke-width="4" stroke-linecap="round" />
            <!-- Leaves -->
            <path d="M250 155 Q230 130 200 140 Q220 165 250 155 Z" fill="#22C55E" />
            <text x="300" y="80" font-family="Tajawal, sans-serif" font-size="28" font-weight="900" fill="#FFFFFF" text-anchor="middle">قطاع الحاصلات الزراعية والموالح</text>
            <text x="300" y="115" font-family="Tajawal, sans-serif" font-size="16" fill="#FDE047" text-anchor="middle">برتقال صيفي وبلدي • ثوم مصري • بصل أحمر وذهبي • بطاطس</text>
            <rect x="180" y="340" width="240" height="35" rx="8" fill="#F59E0B" />
            <text x="300" y="363" font-family="Tajawal, sans-serif" font-size="14" font-weight="700" fill="#0F172A" text-anchor="middle">مطابق للمواصفات الأوروبية Global G.A.P</text>
        </svg>'
    ],
    'sec_food' => [
        'file' => 'sector-food.svg',
        'alt' => 'قطاع السلع والمواد الغذائية المحفوظة',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 400" width="100%" height="100%">
            <defs>
                <linearGradient id="foodBg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1E3A8A" />
                    <stop offset="100%" stop-color="#0F172A" />
                </linearGradient>
            </defs>
            <rect width="600" height="400" fill="url(#foodBg)" />
            <circle cx="300" cy="210" r="120" fill="#1E40AF" opacity="0.4" />
            <!-- Strawberry IQF -->
            <path d="M260 190 C230 190 210 240 250 270 C280 290 300 250 290 210 C285 190 270 190 260 190 Z" fill="#DC2626" stroke="#FECACA" stroke-width="2" />
            <polygon points="255,185 245,175 260,180 275,175 265,185" fill="#16A34A" />
            <!-- White beans & legumes container -->
            <rect x="320" y="190" width="80" height="90" rx="12" fill="#F1F5F9" stroke="#94A3B8" stroke-width="3" />
            <text x="360" y="245" font-family="Tajawal, sans-serif" font-size="18" font-weight="900" fill="#0F172A" text-anchor="middle">بقوليات</text>
            <text x="300" y="80" font-family="Tajawal, sans-serif" font-size="28" font-weight="900" fill="#FFFFFF" text-anchor="middle">قطاع السلع والمواد الغذائية التصديرية</text>
            <text x="300" y="115" font-family="Tajawal, sans-serif" font-size="16" fill="#67E8F9" text-anchor="middle">فراولة مجمدة IQF • فاصوليا بيضاء جافة • خضروات مجمدة</text>
            <rect x="180" y="340" width="240" height="35" rx="8" fill="#0EA5E9" />
            <text x="300" y="363" font-family="Tajawal, sans-serif" font-size="14" font-weight="700" fill="#FFFFFF" text-anchor="middle">تجميد سريع IQF • فرز ليزري فائق</text>
        </svg>'
    ],
    'prod_citrus_charcoal' => [
        'file' => 'product-citrus-charcoal.svg',
        'alt' => 'فحم حمضيات برتقال وليمون ممتاز للشيشة',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 350" width="100%" height="100%">
            <defs>
                <linearGradient id="pCharBg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#18181B" />
                    <stop offset="100%" stop-color="#09090B" />
                </linearGradient>
            </defs>
            <rect width="500" height="350" fill="url(#pCharBg)" rx="12" />
            <circle cx="250" cy="160" r="90" fill="#27272A" />
            <polygon points="170,220 220,130 290,140 310,210 230,240" fill="#111827" stroke="#F59E0B" stroke-width="3" />
            <polygon points="260,220 310,120 370,140 360,220 300,240" fill="#030712" stroke="#E11D48" stroke-width="2" />
            <circle cx="270" cy="180" r="5" fill="#F59E0B" />
            <circle cx="290" cy="165" r="4" fill="#EF4444" />
            <text x="250" y="45" font-family="Tajawal, sans-serif" font-size="20" font-weight="900" fill="#F8FAFC" text-anchor="middle">فحم حمضيات (برتقال وليمون) للشيشة</text>
            <text x="250" y="70" font-family="Montserrat, sans-serif" font-size="13" font-weight="700" fill="#D4AF37" text-anchor="middle">Premium Citrus Wood Charcoal</text>
            <rect x="50" y="275" width="120" height="28" rx="6" fill="#27272A" stroke="#3F3F46" />
            <text x="110" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">كربون: > 80%</text>
            <rect x="190" y="275" width="120" height="28" rx="6" fill="#27272A" stroke="#3F3F46" />
            <text x="250" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">رماد: < 2.5%</text>
            <rect x="330" y="275" width="120" height="28" rx="6" fill="#27272A" stroke="#3F3F46" />
            <text x="390" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">اشتعال: 4-5 ساعات</text>
            <rect x="15" y="15" width="90" height="24" rx="4" fill="#D4AF37" />
            <text x="60" y="32" font-family="Tajawal, sans-serif" font-size="11" font-weight="900" fill="#0F172A" text-anchor="middle">فحم تصدير نخب 1</text>
        </svg>'
    ],
    'prod_bbq_charcoal' => [
        'file' => 'product-bbq-charcoal.svg',
        'alt' => 'فحم كازوارينا وجزورين للمشاوي والمطاعم',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 350" width="100%" height="100%">
            <defs>
                <linearGradient id="pBbqBg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1C1917" />
                    <stop offset="100%" stop-color="#0C0A09" />
                </linearGradient>
            </defs>
            <rect width="500" height="350" fill="url(#pBbqBg)" rx="12" />
            <circle cx="250" cy="160" r="90" fill="#292524" />
            <polygon points="160,230 220,120 290,130 300,220 210,240" fill="#1C1917" stroke="#EA580C" stroke-width="3" />
            <polygon points="270,230 330,110 390,130 380,220 310,240" fill="#0C0A09" stroke="#D97706" stroke-width="2" />
            <circle cx="270" cy="170" r="6" fill="#F97316" />
            <circle cx="310" cy="190" r="5" fill="#EF4444" />
            <text x="250" y="45" font-family="Tajawal, sans-serif" font-size="20" font-weight="900" fill="#F8FAFC" text-anchor="middle">فحم كازوارينا وجزورين للشواء وBBQ</text>
            <text x="250" y="70" font-family="Montserrat, sans-serif" font-size="13" font-weight="700" fill="#F59E0B" text-anchor="middle">Hardwood Casuarina BBQ Charcoal</text>
            <rect x="50" y="275" width="120" height="28" rx="6" fill="#292524" stroke="#44403C" />
            <text x="110" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">أحجام خشنة للمطاعم</text>
            <rect x="190" y="275" width="120" height="28" rx="6" fill="#292524" stroke="#44403C" />
            <text x="250" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">حرارة عالية مستمرة</text>
            <rect x="330" y="275" width="120" height="28" rx="6" fill="#292524" stroke="#44403C" />
            <text x="390" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">بدون شرر أو فرقعة</text>
            <rect x="15" y="15" width="90" height="24" rx="4" fill="#EA580C" />
            <text x="60" y="32" font-family="Tajawal, sans-serif" font-size="11" font-weight="900" fill="#FFFFFF" text-anchor="middle">BBQ Grade A</text>
        </svg>'
    ],
    'prod_oranges' => [
        'file' => 'product-valencia-oranges.svg',
        'alt' => 'برتقال فالنسيا وصيفي مصري للتصدير',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 350" width="100%" height="100%">
            <defs>
                <linearGradient id="pOrBg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#064E3B" />
                    <stop offset="100%" stop-color="#022C22" />
                </linearGradient>
            </defs>
            <rect width="500" height="350" fill="url(#pOrBg)" rx="12" />
            <circle cx="250" cy="165" r="85" fill="#047857" opacity="0.3" />
            <circle cx="210" cy="175" r="60" fill="#F97316" stroke="#FDBA74" stroke-width="3" />
            <circle cx="290" cy="175" r="55" fill="#EA580C" stroke="#FED7AA" stroke-width="3" />
            <path d="M210 115 Q190 90 160 100 Q180 125 210 115 Z" fill="#22C55E" stroke="#15803D" stroke-width="2" />
            <path d="M290 120 Q280 95 250 105 Q270 130 290 120 Z" fill="#16A34A" stroke="#14532D" stroke-width="2" />
            <text x="250" y="45" font-family="Tajawal, sans-serif" font-size="20" font-weight="900" fill="#F8FAFC" text-anchor="middle">برتقال فالنسيا وبصرة للتصدير</text>
            <text x="250" y="70" font-family="Montserrat, sans-serif" font-size="13" font-weight="700" fill="#FDE047" text-anchor="middle">Egyptian Fresh Valencia Oranges</text>
            <rect x="50" y="275" width="120" height="28" rx="6" fill="#065F46" stroke="#047857" />
            <text x="110" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">معايرة: 48-100</text>
            <rect x="190" y="275" width="120" height="28" rx="6" fill="#065F46" stroke="#047857" />
            <text x="250" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">كرتون تلسكوبي 15 كجم</text>
            <rect x="330" y="275" width="120" height="28" rx="6" fill="#065F46" stroke="#047857" />
            <text x="390" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">حاويات مبردة ريفير</text>
            <rect x="15" y="15" width="90" height="24" rx="4" fill="#F59E0B" />
            <text x="60" y="32" font-family="Tajawal, sans-serif" font-size="11" font-weight="900" fill="#0F172A" text-anchor="middle">موالح نخب أول</text>
        </svg>'
    ],
    'prod_garlic' => [
        'file' => 'product-fresh-garlic.svg',
        'alt' => 'ثوم مصري طازج أحمر وأبيض للتصدير',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 350" width="100%" height="100%">
            <defs>
                <linearGradient id="pGarBg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1E293B" />
                    <stop offset="100%" stop-color="#0F172A" />
                </linearGradient>
            </defs>
            <rect width="500" height="350" fill="url(#pGarBg)" rx="12" />
            <circle cx="250" cy="165" r="85" fill="#334155" opacity="0.3" />
            <!-- Garlic head -->
            <path d="M250 230 C200 230 190 160 230 130 C260 110 280 130 300 160 C320 200 290 230 250 230 Z" fill="#F8FAFC" stroke="#E2E8F0" stroke-width="4" />
            <path d="M245 130 L245 95" stroke="#16A34A" stroke-width="6" stroke-linecap="round" />
            <path d="M220 200 Q245 220 270 200" fill="none" stroke="#CBD5E1" stroke-width="2" />
            <text x="250" y="45" font-family="Tajawal, sans-serif" font-size="20" font-weight="900" fill="#F8FAFC" text-anchor="middle">ثوم مصري طازج (أحمر وأبيض)</text>
            <text x="250" y="70" font-family="Montserrat, sans-serif" font-size="13" font-weight="700" fill="#38BDF8" text-anchor="middle">Fresh Egyptian Red &amp; White Garlic</text>
            <rect x="50" y="275" width="120" height="28" rx="6" fill="#334155" stroke="#475569" />
            <text x="110" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">أقطار: 40-70 مم</text>
            <rect x="190" y="275" width="120" height="28" rx="6" fill="#334155" stroke="#475569" />
            <text x="250" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">أكياس مش Mesh / كرتون</text>
            <rect x="330" y="275" width="120" height="28" rx="6" fill="#334155" stroke="#475569" />
            <text x="390" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">مجفف ومقصوص الجذور</text>
            <rect x="15" y="15" width="90" height="24" rx="4" fill="#38BDF8" />
            <text x="60" y="32" font-family="Tajawal, sans-serif" font-size="11" font-weight="900" fill="#0F172A" text-anchor="middle">تصدير أوروبا والخليج</text>
        </svg>'
    ],
    'prod_strawberries' => [
        'file' => 'product-frozen-strawberries.svg',
        'alt' => 'فراولة مجمدة سريعة التجميد IQF للتصدير',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 350" width="100%" height="100%">
            <defs>
                <linearGradient id="pStrBg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#4C0519" />
                    <stop offset="100%" stop-color="#1E1B4B" />
                </linearGradient>
            </defs>
            <rect width="500" height="350" fill="url(#pStrBg)" rx="12" />
            <circle cx="250" cy="165" r="85" fill="#881337" opacity="0.4" />
            <path d="M250 140 C210 140 180 200 230 240 C270 270 300 220 290 170 C280 140 260 140 250 140 Z" fill="#E11D48" stroke="#FDA4AF" stroke-width="3" />
            <polygon points="245,135 230,120 250,130 270,120 255,135" fill="#22C55E" stroke="#15803D" stroke-width="2" />
            <text x="250" y="45" font-family="Tajawal, sans-serif" font-size="20" font-weight="900" fill="#F8FAFC" text-anchor="middle">فراولة مجمدة IQF نخب أول</text>
            <text x="250" y="70" font-family="Montserrat, sans-serif" font-size="13" font-weight="700" fill="#FB7185" text-anchor="middle">Frozen Strawberries Grade A (IQF)</text>
            <rect x="50" y="275" width="120" height="28" rx="6" fill="#881337" stroke="#9F1239" />
            <text x="110" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">تجميد: -18 مئوية</text>
            <rect x="190" y="275" width="120" height="28" rx="6" fill="#881337" stroke="#9F1239" />
            <text x="250" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">كرتون 10 كجم أكياس</text>
            <rect x="330" y="275" width="120" height="28" rx="6" fill="#881337" stroke="#9F1239" />
            <text x="390" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">خالية من المبيدات MRLs</text>
            <rect x="15" y="15" width="90" height="24" rx="4" fill="#FB7185" />
            <text x="60" y="32" font-family="Tajawal, sans-serif" font-size="11" font-weight="900" fill="#0F172A" text-anchor="middle">IQF Grade A</text>
        </svg>'
    ],
    'prod_white_beans' => [
        'file' => 'product-white-beans.svg',
        'alt' => 'فاصوليا بيضاء جافة مصرية عالية الجودة',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 350" width="100%" height="100%">
            <defs>
                <linearGradient id="pBeanBg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1E3A8A" />
                    <stop offset="100%" stop-color="#0F172A" />
                </linearGradient>
            </defs>
            <rect width="500" height="350" fill="url(#pBeanBg)" rx="12" />
            <circle cx="250" cy="165" r="85" fill="#1E40AF" opacity="0.3" />
            <path d="M210 180 C180 180 170 140 200 130 C230 120 250 150 230 180 Z" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="3" />
            <path d="M280 190 C250 190 240 150 270 140 C300 130 320 160 300 190 Z" fill="#F1F5F9" stroke="#94A3B8" stroke-width="3" />
            <text x="250" y="45" font-family="Tajawal, sans-serif" font-size="20" font-weight="900" fill="#F8FAFC" text-anchor="middle">فاصوليا بيضاء جافة عالية النقاوة</text>
            <text x="250" y="70" font-family="Montserrat, sans-serif" font-size="13" font-weight="700" fill="#93C5FD" text-anchor="middle">Egyptian Dry White Kidney Beans</text>
            <rect x="50" y="275" width="120" height="28" rx="6" fill="#1E3A8A" stroke="#2563EB" />
            <text x="110" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">نقاوة: 99% فرز ليزر</text>
            <rect x="190" y="275" width="120" height="28" rx="6" fill="#1E3A8A" stroke="#2563EB" />
            <text x="250" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">شوال 25 كجم بروبلين</text>
            <rect x="330" y="275" width="120" height="28" rx="6" fill="#1E3A8A" stroke="#2563EB" />
            <text x="390" y="294" font-family="Tajawal, sans-serif" font-size="12" fill="#E2E8F0" text-anchor="middle">رطوبة: < 12%</text>
            <rect x="15" y="15" width="90" height="24" rx="4" fill="#60A5FA" />
            <text x="60" y="32" font-family="Tajawal, sans-serif" font-size="11" font-weight="900" fill="#0F172A" text-anchor="middle">بقوليات فرز ليزر</text>
        </svg>'
    ],
    'post_charcoal_guide' => [
        'file' => 'post-charcoal-guide.svg',
        'alt' => 'دليل تصدير وشحن الفحم النباتي عالمياً',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 350" width="100%" height="100%">
            <defs>
                <linearGradient id="post1Bg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#0F172A" />
                    <stop offset="100%" stop-color="#1E293B" />
                </linearGradient>
            </defs>
            <rect width="600" height="350" fill="url(#post1Bg)" rx="12" />
            <rect x="50" y="60" width="500" height="230" rx="10" fill="#1E293B" stroke="#D4AF37" stroke-width="2" />
            <text x="300" y="120" font-family="Tajawal, sans-serif" font-size="24" font-weight="900" fill="#F8FAFC" text-anchor="middle">دليل تصدير الفحم النباتي المصري</text>
            <text x="300" y="160" font-family="Tajawal, sans-serif" font-size="16" fill="#D4AF37" text-anchor="middle">إجراءات السلامة البحرية، شهادات SGS، ومواصفات الفحم للشيشة والمشاوي</text>
            <rect x="220" y="200" width="160" height="40" rx="8" fill="#D4AF37" />
            <text x="300" y="226" font-family="Tajawal, sans-serif" font-size="14" font-weight="900" fill="#0F172A" text-anchor="middle">قراءة المقال التصديري</text>
        </svg>'
    ],
    'post_incoterms_guide' => [
        'file' => 'post-incoterms-guide.svg',
        'alt' => 'دليل شروط التجارة الدولية للمستوردين',
        'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 350" width="100%" height="100%">
            <defs>
                <linearGradient id="post2Bg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#064E3B" />
                    <stop offset="100%" stop-color="#0F172A" />
                </linearGradient>
            </defs>
            <rect width="600" height="350" fill="url(#post2Bg)" rx="12" />
            <rect x="50" y="60" width="500" height="230" rx="10" fill="#065F46" stroke="#34D399" stroke-width="2" />
            <text x="300" y="120" font-family="Tajawal, sans-serif" font-size="24" font-weight="900" fill="#F8FAFC" text-anchor="middle">شروط الشحن الدولي Incoterms</text>
            <text x="300" y="160" font-family="Tajawal, sans-serif" font-size="16" fill="#A7F3D0" text-anchor="middle">الفرق بين FOB و CIF و CFR وكيف تختار الأنسب لميناء وصولك</text>
            <rect x="220" y="200" width="160" height="40" rx="8" fill="#34D399" />
            <text x="300" y="226" font-family="Tajawal, sans-serif" font-size="14" font-weight="900" fill="#064E3B" text-anchor="middle">قراءة المقال اللوجستي</text>
        </svg>'
    ]
];

echo "Generating and saving SVG assets...\n";
$mediaIds = [];

foreach ($assets as $key => $asset) {
    $filePath = $imgDir . '/' . $asset['file'];
    file_put_contents($filePath, trim($asset['svg']));
    $webPath = '/assets/images/' . $asset['file'];

    // Check if existing in media
    $existing = $db->fetchOne("SELECT id FROM media WHERE file_path = ? LIMIT 1", [$webPath]);
    if ($existing) {
        $mediaId = (int)$existing['id'];
    } else {
        $mediaId = $db->insert("INSERT INTO media (uploader_id, original_name, file_name, file_path, file_type, file_size, alt_text, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)", [
            1,
            $asset['file'],
            $asset['file'],
            $webPath,
            'image/svg+xml',
            filesize($filePath),
            $asset['alt']
        ]);
    }
    $mediaIds[$key] = $mediaId;
    echo "Asset [{$key}] saved at {$webPath} (Media ID: {$mediaId})\n";
}

// 2. Link Media IDs to Export Sections
if (isset($mediaIds['sec_charcoal'])) {
    $db->execute("UPDATE export_sections SET featured_image_id = ?, banner_image_id = ? WHERE slug = 'charcoal'", [$mediaIds['sec_charcoal'], $mediaIds['sec_charcoal']]);
}
if (isset($mediaIds['sec_agri'])) {
    $db->execute("UPDATE export_sections SET featured_image_id = ?, banner_image_id = ? WHERE slug = 'agriculture'", [$mediaIds['sec_agri'], $mediaIds['sec_agri']]);
}
if (isset($mediaIds['sec_food'])) {
    $db->execute("UPDATE export_sections SET featured_image_id = ?, banner_image_id = ? WHERE slug = 'foodstuffs'", [$mediaIds['sec_food'], $mediaIds['sec_food']]);
}

// 3. Link Media IDs to Products
if (isset($mediaIds['prod_citrus_charcoal'])) {
    $db->execute("UPDATE products SET featured_image_id = ? WHERE slug LIKE '%citrus%' OR slug LIKE '%orange%'", [$mediaIds['prod_citrus_charcoal']]);
}
if (isset($mediaIds['prod_bbq_charcoal'])) {
    $db->execute("UPDATE products SET featured_image_id = ? WHERE slug LIKE '%bbq%' OR slug LIKE '%casuarina%'", [$mediaIds['prod_bbq_charcoal']]);
}
if (isset($mediaIds['prod_oranges'])) {
    $db->execute("UPDATE products SET featured_image_id = ? WHERE slug LIKE '%orange%' OR slug LIKE '%valencia%'", [$mediaIds['prod_oranges']]);
}
if (isset($mediaIds['prod_garlic'])) {
    $db->execute("UPDATE products SET featured_image_id = ? WHERE slug LIKE '%garlic%'", [$mediaIds['prod_garlic']]);
}
if (isset($mediaIds['prod_strawberries'])) {
    $db->execute("UPDATE products SET featured_image_id = ? WHERE slug LIKE '%strawberr%' OR slug LIKE '%iqf%'", [$mediaIds['prod_strawberries']]);
}
if (isset($mediaIds['prod_white_beans'])) {
    $db->execute("UPDATE products SET featured_image_id = ? WHERE slug LIKE '%bean%' OR slug LIKE '%legume%'", [$mediaIds['prod_white_beans']]);
}

// 4. Link Media IDs to Posts
if (isset($mediaIds['post_charcoal_guide'])) {
    $db->execute("UPDATE posts SET featured_image_id = ? WHERE slug LIKE '%charcoal%'", [$mediaIds['post_charcoal_guide']]);
}
if (isset($mediaIds['post_incoterms_guide'])) {
    $db->execute("UPDATE posts SET featured_image_id = ? WHERE slug LIKE '%incoterm%'", [$mediaIds['post_incoterms_guide']]);
}

echo "=== Visual Assets Seeding Finished Successfully! ===\n";
