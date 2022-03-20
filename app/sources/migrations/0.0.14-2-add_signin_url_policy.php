<?php
    namespace DatabaseMigrator;

    class AddSigninUrlPolicy__2__0_0_14 {
        public function up($db, $log) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='signin-url'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('signin-url', 'pb-auth/signin')");
            } else {
                $log("Entry for policy \"signin-url\" already exists, not overriding it.");
            }
        }

        public function down($db, $log) {
            $log("No need to delete \"signin-url\" policy.");
        }
    }