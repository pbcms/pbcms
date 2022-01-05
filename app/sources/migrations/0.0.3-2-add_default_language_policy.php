<?php
    namespace DatabaseMigrator;

    class AddDefaultLanguagePolicy__2__0_0_3 {
        public function up($db, $log) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='default-language'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('default-language', 'en')");
            } else {
                $log("Entry for policy \"default-language\" already exists, not overriding it.");
            }
        }

        public function down($db, $log) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='default-language'");
            if ($res->num_rows == 0 || $res->fetch_assoc()['value'] === 'en') {
                $db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='default-language'");
            } else {
                $log("Non-default value for policy \"default-language\" was found, not deleting the entry.");
            }
        }
    }