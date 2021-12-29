<?php
    namespace DatabaseMigrator;

    class AddSessionPropertiesColumn__2__0_0_12 {
        public function up($db) {
            $res = $db->query("ALTER TABLE `" . DATABASE_TABLE_PREFIX . "sessions` ADD `properties` JSON NOT NULL DEFAULT ('[]') AFTER `uuid`");
            var_dump($res);
        }

        public function down($up) {
            \Core::PrintLine("No need to remove \"properties\" column from the \"sessions\" table.");
        }
    }