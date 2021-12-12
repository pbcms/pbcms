<?php
    namespace DatabaseMigrator;

    class TablePermissions__5__0_0_1 {
        public function up($db) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "permissions` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `permission` longtext NOT NULL,
                `target` varchar(64) NOT NULL,
                `granted` int(11) NOT NULL DEFAULT 1,
                `created` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated` timestamp NOT NULL DEFAULT current_timestamp()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "permissions` (`permission`, `target`, `granted`) VALUES
                ('site.*', 'grp:1', '1'),
                ('media.*', 'grp:1', '1'),
                ('object.*', 'grp:1', '1'),
                ('policy.*', 'grp:1', '1'),
                ('relation.*', 'grp:1', '1'),
                ('role.*', 'grp:1', '1'),
                ('session.*', 'grp:1', '1'),
                ('token.*', 'grp:1', '1'),
                ('user.*', 'grp:1', '1'),
                ('router.*', 'grp:1', '1');");
        }

        public function down($db) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "permissions`");
        }
    }