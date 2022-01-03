<?php
    namespace DatabaseMigrator;

    class AddAdminModulesPermissions__1__0_0_8 {
        public function up($db, $log) {
            $res = $db->query("SELECT `permission` FROM `" . DATABASE_TABLE_PREFIX . "permissions` WHERE `permission`='module.*' AND `target`='role:1' AND `granted`='1'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "permissions` (`permission`, `target`) VALUES ('module.*', 'role:1')");
            } else {
                $log("Permission \"module.*\" was already granted to the Administrator role.");
            }
        }

        public function down($db) {
            $log("No need to remove permission \"module.*\" from the Administrator role.");
        }
    }