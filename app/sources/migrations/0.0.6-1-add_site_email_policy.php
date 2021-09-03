<?php
    namespace DatabaseMigrator;

    class AddSiteEmailPolicy__1__0_0_6 {
        public function up($db) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='site-email'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('site-email', 'webmaster@localhost')");
            } else {
                \Core::PrintLine("Entry for policy \"site-email\" already exists, not overriding it.");
            }
        }

        public function down($db) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='site-email'");
            if ($res->num_rows == 0 || $res->fetch_assoc()['value'] === 'webmaster@localhost') {
                $db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='site-email'");
            } else {
                \Core::PrintLine("Non-default value for policy \"site-email\" was found, not deleting the entry.");
            }
        }
    }