<?php
    namespace DatabaseMigrator;

    class TablePolicies__6__0_0_1 {
        public function up($db) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "policies` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` varchar(128) NOT NULL UNIQUE,
                `value` longtext NOT NULL,
                `created` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated` timestamp NOT NULL DEFAULT current_timestamp()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $db->query("INSERT INTO `pb_policies` (`name`, `value`) VALUES
                ('site-title', 'PBCMS'),
                ('site-description', 'PHP Basic Content Management System'),
                ('site-location', 'http://localhost/'),
                ('site-indexing', '1'),
                ('usernames-enabled', '1'),
                ('usernames-required', '0'),
                ('usernames-minimum-length', '5'),
                ('usernames-maximum-length', '30'),
                ('signup-allowed', '0'),
                ('password-policy', 'STRONG'),
                ('profilepicture-allowed', '1'),
                ('password-reset-policy', 'REQUESTADMIN'),
                ('user-email-verification', '0'),
                ('allow-stay-signedin', '1');");
        }

        public function down($db) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "policies`");
        }
    }