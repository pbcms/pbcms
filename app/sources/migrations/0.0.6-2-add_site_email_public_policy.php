<?php
    namespace DatabaseMigrator;

    class AddSiteEmailPublicPolicy__2__0_0_6 {
        public function up($db, $log) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='site-email-public'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('site-email-public', 1)");
            } else {
                $log("Entry for policy \"site-email-public\" already exists, not overriding it.");
            }
        }

        public function down($db, $log) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='site-email-public'");
            if ($res->num_rows == 0 || intval($res->fetch_assoc()['value']) === 1) {
                $db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='site-email-public'");
            } else {
                $log("Non-default value for policy \"site-email-public\" was found, not deleting the entry.");
            }
        }
    }