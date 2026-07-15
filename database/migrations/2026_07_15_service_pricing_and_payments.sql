-- =====================================================================
--  Migration: service pricing + online payments (Instamojo checkout)
--  Adds a `price` column to services so pricing can be displayed on the
--  website, and a `payments` table that records every checkout attempt
--  and its final status as reported by the payment gateway.
--
--  Import via cPanel > phpMyAdmin or the MySQL CLI:
--      mysql -u USER -p DATABASE < 2026_07_15_service_pricing_and_payments.sql
-- =====================================================================

ALTER TABLE `services`
  ADD COLUMN `price` DECIMAL(10,2) NULL AFTER `description`;

CREATE TABLE IF NOT EXISTS `payments` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`          INT UNSIGNED NOT NULL,
  `service_id`         INT UNSIGNED NULL,
  `service_title`      VARCHAR(150) NOT NULL,
  `buyer_name`         VARCHAR(150) NOT NULL,
  `email`              VARCHAR(190) NOT NULL,
  `phone`              VARCHAR(20)  NOT NULL,
  `amount`             DECIMAL(10,2) NOT NULL,
  `payment_request_id` VARCHAR(64)  NULL,
  `payment_id`         VARCHAR(64)  NULL,
  `status`             ENUM('created','success','failed') NOT NULL DEFAULT 'created',
  `created_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payment_tenant` (`tenant_id`),
  KEY `idx_payment_request` (`payment_request_id`),
  CONSTRAINT `fk_payment_tenant` FOREIGN KEY (`tenant_id`)
    REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
