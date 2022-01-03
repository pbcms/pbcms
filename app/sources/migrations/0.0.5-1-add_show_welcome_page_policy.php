<?php
    namespace DatabaseMigrator;

    class AddShowWelcomePagePolicy__1__0_0_5 {
        public function up($db, $log) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='show-welcome-page'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('show-welcome-page', 1)");
            } else {
                $log("Entry for policy \"show-welcome-page\" already exists, not overriding it.");
            }
        }

        public function down($db, $log) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='show-welcome-page'");
            if ($res->num_rows == 0 || intval($res->fetch_assoc()['value']) === 1) {
                $db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='show-welcome-page'");
            } else {
                $log("Non-default value for policy \"show-welcome-page\" was found, not deleting the entry.");
            }
        }
    }