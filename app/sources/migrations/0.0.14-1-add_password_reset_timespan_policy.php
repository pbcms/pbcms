<?php
    namespace DatabaseMigrator;

    class AddPasswordResetTimespanPolicy__1__0_0_14 {
        public function up($db, $log) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='password-reset-timespan'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('password-reset-timespan', '600')");
            } else {
                $log("Entry for policy \"password-reset-timespan\" already exists, not overriding it.");
            }
        }

        public function down($db, $log) {
            $log("No need to delete \"password-reset-timespan\" policy.");
        }
    }