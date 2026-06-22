-- =====================================================================
--  Migration: webinar registration fields
--  Adds the new columns used by the parent/child webinar registration
--  form. Safe to run against an existing database (existing data is kept;
--  full_name/mobile are now used for Parent Name / WhatsApp Number).
--
--  Import via cPanel > phpMyAdmin or the MySQL CLI:
--      mysql -u USER -p DATABASE < 2026_06_22_webinar_registration_fields.sql
-- =====================================================================

ALTER TABLE `registrations`
  ADD COLUMN `child_name`  VARCHAR(150) NULL AFTER `email`,
  ADD COLUMN `grade`       VARCHAR(60)  NULL AFTER `child_name`,
  ADD COLUMN `syllabus`    VARCHAR(60)  NULL AFTER `grade`,
  ADD COLUMN `heard_about` VARCHAR(60)  NULL AFTER `city`;
