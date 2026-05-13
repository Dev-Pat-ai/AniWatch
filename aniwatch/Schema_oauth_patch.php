-- ═══════════════════════════════════════════════════════════════
--  schema_oauth_patch.sql
--  Run this ONCE against your database to add the OAuth columns.
--  Only needed if your current `users` table doesn't have them.
-- ═══════════════════════════════════════════════════════════════

-- Add the OAuth provider name (google / facebook / github / linkedin)
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS provider    VARCHAR(20)  DEFAULT NULL AFTER email;

-- Add the unique user ID returned by the provider
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS provider_id VARCHAR(128) DEFAULT NULL AFTER provider;

-- Allow password to be NULL for OAuth-only accounts
ALTER TABLE users
    MODIFY COLUMN password VARCHAR(255) DEFAULT NULL;

-- Optional: index for fast provider look-ups
CREATE INDEX IF NOT EXISTS idx_users_provider_id
    ON users (provider, provider_id);