<?php
    namespace DatabaseMigrator;

    class AddMediaPublicOption__3__0_0_11 {
        public function up($db, $log) {
            $res = $db->query("ALTER TABLE `" . DATABASE_TABLE_PREFIX . "media` ADD COLUMN `public` int(11) NOT NULL DEFAULT '1'");
        }

        public function down($db, $log) {
            $log("No need to remove \"public\" column from the \"media\" table.");
        }
    }