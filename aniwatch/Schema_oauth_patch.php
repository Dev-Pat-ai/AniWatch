-- ═══════════════════════════════════════════════════════════════
--  schema_oauth_patch.sql
--  Run this ONCE in phpMyAdmin (or MySQL CLI) BEFORE testing OAuth.
--
--  Fixes:
--    1. Allow password to be NULL  (OAuth users have no password)
--    2. Add  provider     column   (google / facebook / github / linkedin)
--    3. Add  provider_id  column   (the user's ID from the provider)
--    4. Add an index for fast OAuth look-ups
-- ═══════════════════════════════════════════════════════════════
 
-- 1. Allow NULL passwords (OAuth users won't have one)
ALTER TABLE users
    MODIFY COLUMN password VARCHAR(255) DEFAULT NULL;
 
-- 2. Add provider name column
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS provider VARCHAR(20) DEFAULT NULL
    AFTER email;
 
-- 3. Add provider user-ID column
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS provider_id VARCHAR(128) DEFAULT NULL
    AFTER provider;
 
-- 4. Index for fast look-up by provider + provider_id
CREATE INDEX IF NOT EXISTS idx_users_provider
    ON users (provider, provider_id);

-- 5. Add roles for admin/user access
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS role ENUM('user','admin') DEFAULT 'user'
    AFTER email;

-- Promote one account manually after replacing the username.
-- UPDATE users SET role = 'admin' WHERE username = 'your_admin_username';
 
