-- ============================================================================
-- Mock Master test contacts (run in Navicat)
--
-- Sets ONE email / phone / country code on every Mock Master student, so test
-- emails, SMS and WhatsApp from XPlatform reach you instead of real students.
-- Run it AFTER "Sync Data" — a sync brings back the live student details.
--
-- How: Navicat -> open a Query on the analytics_platform database ->
--      paste this file (or File > Open) -> change the 3 values below -> Run.
--
-- Not touched: normalized_email (must be unique per student) and login history.
-- Phone is stored as a number, so a leading 0 is dropped (0406870923 -> 406870923).
-- ============================================================================

USE analytics_platform;

-- ===== Change these to your test details =====
SET @email        = 'xplatformintern@gmail.com';
SET @phone        = '0406870923';
SET @country_code = '+61';
-- =============================================

-- Students
UPDATE mm_studentuser
SET email        = TRIM(@email),
    phone        = CAST(REPLACE(REPLACE(TRIM(@phone), ' ', ''), '-', '') AS UNSIGNED),
    country_code = TRIM(@country_code);

-- Deleted students (same columns; often empty)
UPDATE mm_deleted_students
SET email        = TRIM(@email),
    phone        = CAST(REPLACE(REPLACE(TRIM(@phone), ' ', ''), '-', '') AS UNSIGNED),
    country_code = TRIM(@country_code);

-- Check: should show one row with every student
SELECT email, country_code, phone, COUNT(*) AS students
FROM mm_studentuser
GROUP BY email, country_code, phone
ORDER BY students DESC
LIMIT 5;
