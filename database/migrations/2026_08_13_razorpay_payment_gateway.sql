-- =====================================================================
--  Migration: switch payment gateway from Instamojo to Razorpay
--
--  Run this after deploying the updated code:
--      mysql -u USER -p DATABASE < 2026_08_13_razorpay_payment_gateway.sql
-- =====================================================================

-- Add Razorpay settings for every active tenant that does not have them yet.
INSERT IGNORE INTO `settings` (`tenant_id`, `setting_key`, `setting_value`)
SELECT `id`, 'razorpay_key_id',     '' FROM `tenants` WHERE `status` = 'active';

INSERT IGNORE INTO `settings` (`tenant_id`, `setting_key`, `setting_value`)
SELECT `id`, 'razorpay_key_secret', '' FROM `tenants` WHERE `status` = 'active';

INSERT IGNORE INTO `settings` (`tenant_id`, `setting_key`, `setting_value`)
SELECT `id`, 'razorpay_mode',       'test' FROM `tenants` WHERE `status` = 'active';

-- Remove old Instamojo settings (safe to delete once Razorpay is configured).
-- Uncomment these lines after verifying Razorpay is working:
-- DELETE FROM `settings` WHERE `setting_key` IN ('instamojo_api_key','instamojo_auth_token','instamojo_mode');
