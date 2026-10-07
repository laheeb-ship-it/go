-- Golden Bird Charcoal CMS Database Schema
-- Pure MySQL / MariaDB (InnoDB Engine)

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Roles & Permissions Table
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `permissions` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `last_login_at` DATETIME NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_users_role` (`role_id`),
    INDEX `idx_users_status` (`status`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Media Library Table
CREATE TABLE IF NOT EXISTS `media` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uploader_id` INT UNSIGNED NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL UNIQUE,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(100) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL,
    `alt_text` VARCHAR(255) NULL,
    `caption` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_media_file_type` (`file_type`),
    INDEX `idx_media_created_at` (`created_at`),
    CONSTRAINT `fk_media_uploader` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Dynamic Export Sections Table
CREATE TABLE IF NOT EXISTS `export_sections` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `short_description` TEXT NULL,
    `full_description` LONGTEXT NULL,
    `featured_image_id` INT UNSIGNED NULL,
    `banner_image_id` INT UNSIGNED NULL,
    `icon_code` VARCHAR(50) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
    `seo_title` VARCHAR(255) NULL,
    `seo_description` VARCHAR(255) NULL,
    `seo_keywords` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_sections_status_order` (`status`, `sort_order`),
    CONSTRAINT `fk_sections_featured_img` FOREIGN KEY (`featured_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_sections_banner_img` FOREIGN KEY (`banner_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Products Table
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `export_section_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `sku` VARCHAR(50) NULL,
    `short_description` TEXT NULL,
    `full_description` LONGTEXT NULL,
    `specifications` LONGTEXT NULL,
    `packaging_options` TEXT NULL,
    `origin_country` VARCHAR(100) NOT NULL DEFAULT 'Egypt',
    `featured_image_id` INT UNSIGNED NULL,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
    `seo_title` VARCHAR(255) NULL,
    `seo_description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_products_section` (`export_section_id`),
    INDEX `idx_products_status_featured` (`status`, `is_featured`, `sort_order`),
    CONSTRAINT `fk_products_section` FOREIGN KEY (`export_section_id`) REFERENCES `export_sections` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_products_featured_img` FOREIGN KEY (`featured_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Product Media Gallery Table
CREATE TABLE IF NOT EXISTS `product_gallery` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `media_id` INT UNSIGNED NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `idx_gallery_product` (`product_id`),
    INDEX `idx_gallery_media` (`media_id`),
    CONSTRAINT `fk_gallery_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_gallery_media` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Pages Table
CREATE TABLE IF NOT EXISTS `pages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `content` LONGTEXT NULL,
    `template` VARCHAR(50) NOT NULL DEFAULT 'default',
    `featured_image_id` INT UNSIGNED NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    `seo_title` VARCHAR(255) NULL,
    `seo_description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_pages_status` (`status`),
    INDEX `idx_pages_sort` (`sort_order`),
    CONSTRAINT `fk_pages_featured_img` FOREIGN KEY (`featured_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Posts / Blog Table
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `author_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `excerpt` TEXT NULL,
    `content` LONGTEXT NULL,
    `featured_image_id` INT UNSIGNED NULL,
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
    `published_at` DATETIME NULL,
    `seo_title` VARCHAR(255) NULL,
    `seo_description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_posts_author` (`author_id`),
    INDEX `idx_posts_status_published` (`status`, `published_at`),
    CONSTRAINT `fk_posts_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_posts_featured_img` FOREIGN KEY (`featured_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Quote Requests Table (Export Inquiries)
CREATE TABLE IF NOT EXISTS `quote_requests` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NULL,
    `export_section_id` INT UNSIGNED NULL,
    `company_name` VARCHAR(150) NOT NULL,
    `contact_person` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(50) NOT NULL,
    `country` VARCHAR(100) NOT NULL,
    `destination_port` VARCHAR(150) NOT NULL,
    `target_quantity` VARCHAR(100) NOT NULL,
    `incoterms` ENUM('FOB', 'CIF', 'CFR', 'EXW', 'OTHER') NOT NULL DEFAULT 'FOB',
    `packaging_requirements` TEXT NULL,
    `notes` TEXT NULL,
    `status` ENUM('new', 'in_review', 'contacted', 'quoted', 'closed') NOT NULL DEFAULT 'new',
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_quotes_status` (`status`),
    INDEX `idx_quotes_created_at` (`created_at`),
    CONSTRAINT `fk_quotes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_quotes_section` FOREIGN KEY (`export_section_id`) REFERENCES `export_sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Contact Messages Table
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(50) NULL,
    `subject` VARCHAR(200) NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('unread', 'read', 'replied', 'archived') NOT NULL DEFAULT 'unread',
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_contact_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Website Settings Table (Key-Value)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` LONGTEXT NULL,
    `group_name` VARCHAR(50) NOT NULL DEFAULT 'general',
    `autoload` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    INDEX `idx_settings_group` (`group_name`),
    INDEX `idx_settings_autoload` (`autoload`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Menus Table
CREATE TABLE IF NOT EXISTS `menus` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(100) NOT NULL,
    `location` VARCHAR(50) NOT NULL UNIQUE,
    `items_json` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Activity Logs Table
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_logs_user` (`user_id`),
    INDEX `idx_logs_created_at` (`created_at`),
    CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Certificates Table
CREATE TABLE IF NOT EXISTS `certificates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `title_en` VARCHAR(255) NULL,
    `issuer` VARCHAR(255) NULL,
    `cert_number` VARCHAR(100) NULL,
    `image` VARCHAR(255) NULL,
    `image_url` VARCHAR(255) NULL,
    `pdf_file` VARCHAR(255) NULL,
    `issue_date` VARCHAR(50) NULL,
    `expiry_date` VARCHAR(50) NULL,
    `issue_year` VARCHAR(10) NULL,
    `description` TEXT NULL,
    `badge_color` VARCHAR(30) NOT NULL DEFAULT '#10b981',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_certs_active` (`is_active`),
    INDEX `idx_certs_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. FAQs Table
CREATE TABLE IF NOT EXISTS `faqs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question` TEXT NOT NULL,
    `question_en` TEXT NULL,
    `answer` LONGTEXT NOT NULL,
    `answer_en` LONGTEXT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'general',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_faqs_active` (`is_active`),
    INDEX `idx_faqs_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Invoices Table (Proforma & Commercial)
CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_number` VARCHAR(100) NOT NULL UNIQUE,
    `type` VARCHAR(30) NOT NULL DEFAULT 'proforma',
    `title` VARCHAR(255) NOT NULL,
    `buyer_name` VARCHAR(255) NOT NULL,
    `buyer_company` VARCHAR(255) NULL,
    `buyer_country` VARCHAR(100) NOT NULL DEFAULT 'مصر',
    `buyer_email` VARCHAR(150) NULL,
    `buyer_phone` VARCHAR(50) NULL,
    `buyer_tax_id` VARCHAR(100) NULL,
    `buyer_address` TEXT NULL,
    `incoterm` VARCHAR(20) NOT NULL DEFAULT 'FOB',
    `port_of_loading` VARCHAR(150) NOT NULL DEFAULT 'ميناء الإسكندرية، مصر',
    `port_of_discharge` VARCHAR(150) NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `exchange_rate` DECIMAL(10,4) NOT NULL DEFAULT 1.0000,
    `items_json` LONGTEXT NULL,
    `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `freight_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `insurance_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `discount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `payment_terms` TEXT NULL,
    `bank_details` TEXT NULL,
    `notes` TEXT NULL,
    `template` VARCHAR(50) NOT NULL DEFAULT 'classic_gold',
    `status` VARCHAR(30) NOT NULL DEFAULT 'sent',
    `issue_date` VARCHAR(50) NOT NULL,
    `due_date` VARCHAR(50) NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_invoices_num` (`invoice_number`),
    INDEX `idx_invoices_type` (`type`),
    INDEX `idx_invoices_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Contracts Table (Export Contracts & Agreements)
CREATE TABLE IF NOT EXISTS `contracts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contract_number` VARCHAR(100) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `seller_name` VARCHAR(255) NOT NULL DEFAULT 'شركة جولدن بيرد لتصدير الفحم النباتي والتجارة الدولية',
    `seller_representative` VARCHAR(150) NOT NULL DEFAULT 'المدير العام والتنفيذي',
    `buyer_name` VARCHAR(255) NOT NULL,
    `buyer_company` VARCHAR(255) NULL,
    `buyer_country` VARCHAR(100) NOT NULL DEFAULT 'المملكة العربية السعودية',
    `buyer_representative` VARCHAR(150) NULL,
    `buyer_phone` VARCHAR(50) NULL,
    `buyer_email` VARCHAR(150) NULL,
    `buyer_address` TEXT NULL,
    `commodity` VARCHAR(255) NOT NULL,
    `quantity` VARCHAR(100) NOT NULL,
    `total_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `incoterm` VARCHAR(20) NOT NULL DEFAULT 'FOB',
    `shipping_port` VARCHAR(150) NOT NULL DEFAULT 'ميناء الإسكندرية / دمياط، مصر',
    `destination_port` VARCHAR(150) NULL,
    `delivery_schedule` TEXT NULL,
    `payment_method` TEXT NULL,
    `inspection_agency` VARCHAR(150) NOT NULL DEFAULT 'SGS International / معهد فحص الصادرات المصرية',
    `clauses_json` LONGTEXT NULL,
    `terms_conditions` LONGTEXT NULL,
    `template` VARCHAR(50) NOT NULL DEFAULT 'bilateral_gold',
    `status` VARCHAR(30) NOT NULL DEFAULT 'active',
    `start_date` VARCHAR(50) NOT NULL,
    `end_date` VARCHAR(50) NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_contracts_num` (`contract_number`),
    INDEX `idx_contracts_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Sliders Table
CREATE TABLE IF NOT EXISTS `sliders` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `title_en` VARCHAR(255) NULL,
    `subtitle` TEXT NULL,
    `badge_text` VARCHAR(100) NULL,
    `slider_type` VARCHAR(50) NOT NULL DEFAULT 'content',
    `placement` VARCHAR(50) NOT NULL DEFAULT 'home_hero',
    `image_url` VARCHAR(500) NULL,
    `link_url` VARCHAR(255) NULL,
    `link_text` VARCHAR(100) NULL,
    `secondary_link_url` VARCHAR(255) NULL,
    `secondary_link_text` VARCHAR(100) NULL,
    `target_item_id` INT UNSIGNED NULL,
    `item_limit` INT NOT NULL DEFAULT 5,
    `overlay_opacity` INT NOT NULL DEFAULT 55,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_sliders_placement` (`placement`),
    INDEX `idx_sliders_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Privacy-conscious Real User Web Vitals
CREATE TABLE IF NOT EXISTS `web_vitals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `url_path` VARCHAR(512) NOT NULL,
    `device_type` ENUM('mobile', 'desktop', 'tablet', 'unknown') NOT NULL DEFAULT 'unknown',
    `connection_type` VARCHAR(32) NULL,
    `lcp_ms` DECIMAL(10,2) NULL,
    `inp_ms` DECIMAL(10,2) NULL,
    `cls_value` DECIMAL(8,4) NULL,
    `fcp_ms` DECIMAL(10,2) NULL,
    `ttfb_ms` DECIMAL(10,2) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_vitals_created_at` (`created_at`),
    INDEX `idx_vitals_url_device` (`url_path`(191), `device_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
