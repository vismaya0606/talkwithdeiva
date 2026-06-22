-- =====================================================================
--  Multi-Tenant Personal Brand / Coaching Website
--  Complete MySQL Schema (MySQL 5.7+ / MariaDB 10.2+ compatible)
--
--  Import this file via cPanel > phpMyAdmin or the MySQL CLI:
--      mysql -u USER -p DATABASE < schema.sql
--
--  Every business table carries a `tenant_id` so a single codebase can
--  serve many customers (multi-tenant SaaS).  A Super Admin manages the
--  tenants; each tenant has its own admin login, theme, services,
--  testimonials, gallery and registrations.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
--  tenants
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tenants` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150) NOT NULL,
  `domain`     VARCHAR(190) NOT NULL,
  `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenant_domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  admins  (role = superadmin has tenant_id = NULL)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`  INT UNSIGNED NULL,
  `name`       VARCHAR(120) NOT NULL,
  `username`   VARCHAR(120) NOT NULL,
  `email`      VARCHAR(190) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('superadmin','admin') NOT NULL DEFAULT 'admin',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_username` (`username`),
  KEY `idx_admin_tenant` (`tenant_id`),
  CONSTRAINT `fk_admin_tenant` FOREIGN KEY (`tenant_id`)
    REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  settings  (key/value per tenant: theme colours, logo, SEO, etc.)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     INT UNSIGNED NOT NULL,
  `setting_key`   VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting` (`tenant_id`,`setting_key`),
  KEY `idx_setting_tenant` (`tenant_id`),
  CONSTRAINT `fk_setting_tenant` FOREIGN KEY (`tenant_id`)
    REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  services
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     INT UNSIGNED NOT NULL,
  `title`         VARCHAR(150) NOT NULL,
  `description`   TEXT NULL,
  `icon`          VARCHAR(80) NOT NULL DEFAULT 'bi-star',
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_service_tenant` (`tenant_id`),
  CONSTRAINT `fk_service_tenant` FOREIGN KEY (`tenant_id`)
    REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  testimonials
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     INT UNSIGNED NOT NULL,
  `name`          VARCHAR(120) NOT NULL,
  `designation`   VARCHAR(150) NULL,
  `testimonial`   TEXT NOT NULL,
  `photo`         VARCHAR(255) NULL,
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_testi_tenant` (`tenant_id`),
  CONSTRAINT `fk_testi_tenant` FOREIGN KEY (`tenant_id`)
    REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  gallery
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     INT UNSIGNED NOT NULL,
  `image`         VARCHAR(255) NOT NULL,
  `caption`       VARCHAR(190) NULL,
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gallery_tenant` (`tenant_id`),
  CONSTRAINT `fk_gallery_tenant` FOREIGN KEY (`tenant_id`)
    REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  registrations
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `registrations` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`         INT UNSIGNED NOT NULL,
  `full_name`         VARCHAR(150) NOT NULL,   -- Parent Name
  `mobile`            VARCHAR(20) NOT NULL,    -- WhatsApp Number
  `email`             VARCHAR(190) NULL,
  `child_name`        VARCHAR(150) NULL,
  `grade`             VARCHAR(60) NULL,
  `syllabus`          VARCHAR(60) NULL,
  `city`              VARCHAR(120) NULL,
  `heard_about`       VARCHAR(60) NULL,
  `state`             VARCHAR(120) NULL,
  `profession`        VARCHAR(150) NULL,
  `interested_service` VARCHAR(190) NULL,
  `message`           TEXT NULL,               -- Primary question / expectation
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reg_tenant` (`tenant_id`),
  KEY `idx_reg_created` (`created_at`),
  CONSTRAINT `fk_reg_tenant` FOREIGN KEY (`tenant_id`)
    REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
--  SEED DATA
--  Default Super Admin .... username: superadmin   password: super123
--  Default Tenant Admin ... username: admin         password: admin123
--  >>> CHANGE THESE PASSWORDS IMMEDIATELY AFTER INSTALL <<<
-- =====================================================================

INSERT INTO `tenants` (`id`,`name`,`domain`,`status`) VALUES
  (1, 'Demo Tenant', 'localhost', 'active');

INSERT INTO `admins` (`tenant_id`,`name`,`username`,`email`,`password`,`role`) VALUES
  (NULL, 'Super Administrator', 'superadmin', 'super@example.com',
   '$2y$12$810BPLKWrlqo6n70qBkmWufbycO1fi2YbUIqnH1/H6WWxyqYQUXQ2', 'superadmin'),
  (1, 'Site Administrator', 'admin', 'admin@example.com',
   '$2y$12$fU0U1Onkf0sqEqU1tOpcaucx5uXBzouY7aUvJz7DAPN4K269UrfRC', 'admin');

INSERT INTO `settings` (`tenant_id`,`setting_key`,`setting_value`) VALUES
  (1,'site_name','Your Name'),
  (1,'primary_color','#E8112D'),
  (1,'secondary_color','#F2867D'),
  (1,'logo',''),
  (1,'favicon',''),
  (1,'footer_text','© 2026 Your Name. All rights reserved.'),
  (1,'hero_eyebrow','Hello, I am'),
  (1,'hero_title','Your Name'),
  (1,'hero_subtitle','Entrepreneur • Mentor • Public Speaker'),
  (1,'hero_tagline','Empowering people to achieve their full potential.'),
  (1,'hero_image',''),
  (1,'hero_video',''),
  (1,'social_facebook',''),
  (1,'social_instagram',''),
  (1,'social_youtube',''),
  (1,'social_twitter',''),
  (1,'social_linkedin',''),
  (1,'about_title','About Me'),
  (1,'about_content','I am a passionate professional dedicated to helping individuals and organisations grow. With years of experience, I bring vision, energy and results.'),
  (1,'achievements','15+ Years Experience\n500+ People Mentored\n50+ Events Delivered'),
  (1,'contact_phone','+91 90000 00000'),
  (1,'contact_whatsapp','919000000000'),
  (1,'contact_email','contact@example.com'),
  (1,'contact_address','Chennai, Tamil Nadu, India'),
  (1,'meta_title','Your Name | Entrepreneur & Mentor'),
  (1,'meta_description','Official website of Your Name — entrepreneur, mentor and public speaker. Explore services, gallery and register today.'),
  (1,'meta_keywords','mentor, entrepreneur, public speaker, coaching'),
  (1,'og_image','');

INSERT INTO `services` (`tenant_id`,`title`,`description`,`icon`,`display_order`) VALUES
  (1,'Mentorship','One-on-one mentorship to help you reach your personal and professional goals.','bi-people-fill',1),
  (1,'Public Speaking','Inspiring keynote sessions for events, colleges and corporates.','bi-mic-fill',2),
  (1,'Business Consulting','Strategic guidance to grow and scale your business.','bi-graph-up-arrow',3),
  (1,'Workshops','Hands-on workshops on leadership, productivity and growth.','bi-easel-fill',4);

INSERT INTO `testimonials` (`tenant_id`,`name`,`designation`,`testimonial`,`display_order`) VALUES
  (1,'Ramesh Kumar','Business Owner','The mentorship completely changed how I run my company. Highly recommended!',1),
  (1,'Priya S','Student','An incredible speaker who truly inspires. I left motivated and focused.',2),
  (1,'Arun Vijay','Startup Founder','Practical, honest and result-oriented consulting. Worth every minute.',3);

INSERT INTO `gallery` (`tenant_id`,`image`,`caption`,`display_order`) VALUES
  (1,'https://picsum.photos/seed/g1/600/400','Keynote Session',1),
  (1,'https://picsum.photos/seed/g2/600/400','Workshop',2),
  (1,'https://picsum.photos/seed/g3/600/400','Award Ceremony',3),
  (1,'https://picsum.photos/seed/g4/600/400','Team Meet',4);

INSERT INTO `registrations`
  (`tenant_id`,`full_name`,`mobile`,`email`,`city`,`state`,`profession`,`interested_service`,`message`) VALUES
  (1,'Sample Lead','9876543210','lead@example.com','Coimbatore','Tamil Nadu','Engineer','Mentorship','Looking forward to joining.');
