<?php
    namespace DatabaseMigrator;

    class AddDefaultTemplateProvider__1__0_0_2 {
        public function up($db) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='default-template-provider'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('default-template-provider', 'system')");
            } else {
                \Core::PrintLine("Entry for policy \"default-template-provider\" already exists, not overriding it.");
            }
        }

        public function down($db) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='default-template-provider'");
            if ($res->num_rows == 0 || $res->fetch_assoc()['value'] == 'system') {
                $db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='default-template-provider'");
            } else {
                \Core::PrintLine("Non-default value for policy \"default-template-provider\" was found, not deleting the entry.");
            }
        }
    }