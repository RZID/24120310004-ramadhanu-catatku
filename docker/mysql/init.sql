-- Runs once, on first start of an empty MySQL data volume.
-- The application database itself is created by the image via MYSQL_DATABASE;
-- this file only adds the separate database used by the test suite.
-- The username below mirrors DB_USERNAME in .env.example.
CREATE DATABASE IF NOT EXISTS `catatku_testing`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON `catatku_testing`.* TO 'catatku'@'%';

FLUSH PRIVILEGES;
