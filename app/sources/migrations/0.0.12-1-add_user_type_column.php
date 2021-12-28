<?php
    namespace DatabaseMigrator;

    class AddUserTypeColumn__1__0_0_12 {
        public function up($db) {
            $res = $db->query("ALTER TABLE `" . DATABASE_TABLE_PREFIX . "users` ADD `type` VARCHAR(128) NOT NULL DEFAULT 'local' AFTER `username`");
        }

        public function down($up) {
            \Core::PrintLine("No need to remove \"type\" column from the \"users\" table.");
        }
    }