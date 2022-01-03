<?php
    namespace DatabaseMigrator;

    class AddUserTypeColumn__1__0_0_12 {
        public function up($db, $log) {
            $res = $db->query("ALTER TABLE `" . DATABASE_TABLE_PREFIX . "users` ADD `type` VARCHAR(128) NOT NULL DEFAULT 'local' AFTER `username`");
        }

        public function down($up, $log) {
            $log("No need to remove \"type\" column from the \"users\" table.");
        }
    }