<?php
    namespace DatabaseMigrator;

    class TableObjectProperties__4__0_0_1 {
        public function up($db, $log) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "object-properties` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `object` int(11) NOT NULL,
                `property` varchar(256) NOT NULL,
                `value` longtext NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "object-properties` (`id`, `object`, `property`, `value`) VALUES
                (4, 1, 'NONE', 'score=0'),
                (5, 1, 'WEAK', 'score=0.6,length'),
                (6, 1, 'MEDIUM', 'score=0.8,uppercase,lowercase,length'),
                (7, 1, 'STRONG', 'score=1');");
        }

        public function down($db, $log) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "object-properties`");
        }
    }