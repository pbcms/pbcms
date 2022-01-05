<?php
    namespace DatabaseMigrator;

    class AddSessionDefaultExpirationPolicy__3__0_0_2 {
        public function up($db, $log) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='session-default-expiration'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('session-default-expiration', 86400)");
            } else {
                $log("Entry for policy \"session-default-expiration\" already exists, not overriding it.");
            }
        }

        public function down($db, $log) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='session-default-expiration'");
            if ($res->num_rows == 0 || intval($res->fetch_assoc()['value']) === 86400) {
                $db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='session-default-expiration'");
            } else {
                $log("Non-default value for policy \"session-default-expiration\" was found, not deleting the entry.");
            }
        }
    }