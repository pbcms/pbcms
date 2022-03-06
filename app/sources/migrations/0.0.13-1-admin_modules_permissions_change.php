<?php
    namespace DatabaseMigrator;

    /**
     * I know this migration feels like it's a duplicate, but it's not. 
     * The 'module.*' permission can now be used by a module to check if a user has a permission (ex. module.sample-module.change-setting.or.whatever).
     * The 'modules.*' will superseed the 'module.*' permission as it will now be used to check if a user has permission to alter modules.
     */

    class AdminModulesPermissionsChange__1__0_0_13 {
        public function up($db, $log) {
            $res = $db->query("SELECT `permission` FROM `" . DATABASE_TABLE_PREFIX . "permissions` WHERE `permission`='modules.*' AND `target`='role:1' AND `granted`='1'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "permissions` (`permission`, `target`) VALUES ('modules.*', 'role:1')");
            } else {
                $log("Permission \"modules.*\" was already granted to the Administrator role.");
            }
        }

        public function down($db) {
            $log("No need to remove permission \"modules.*\" from the Administrator role.");
        }
    }