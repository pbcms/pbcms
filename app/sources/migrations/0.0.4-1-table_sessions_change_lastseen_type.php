<?php
    namespace DatabaseMigrator;

    class TableSessionsChangeLastseenType__1__0_0_4 {
        public function up($db, $log) {
            $db->query("ALTER TABLE `" . DATABASE_TABLE_PREFIX . "sessions` CHANGE `lastSeen` `lastSeen` BIGINT NOT NULL");
        }

        public function down($db, $log) {
            $log("No need to revert changes, was a bug in database structure and will work in prior versions too.");
        }
    }