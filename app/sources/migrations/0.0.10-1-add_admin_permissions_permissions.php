<?php
    namespace DatabaseMigrator;

    class AddAdminPermissionsPermissions__1__0_0_10 {
        public function up($db) {
            $res = $db->query("SELECT `permission` FROM `" . DATABASE_TABLE_PREFIX . "permissions` WHERE `permission`='permission.*' AND `target`='role:1' AND `granted`='1'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "permissions` (`permission`, `target`) VALUES ('permission.*', 'role:1')");
            } else {
                \Core::PrintLine("Permission \"permission.*\" was already granted to the Administrator role.");
            }
        }

        public function down($db) {
            \Core::PrintLine("No need to remove permission \"permission.*\" from the Administrator role.");
        }
    }