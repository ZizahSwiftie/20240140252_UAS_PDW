USE complaint_system;

ALTER TABLE reports
    ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,8) DEFAULT NULL AFTER admin_response,
    ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) DEFAULT NULL AFTER latitude;
