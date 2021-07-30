<?php
    namespace DatabaseMigrator;

    class AddPbcmsSafeMode__2__0_0_2 {
        public function up($db) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='pbcms-safe-mode'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('pbcms-safe-mode', 0)");
            } else {
                echo "Entry for policy \"pbcms-safe-mode\" already exists, not overriding it." . PHP_EOL;
            }
        }

        public function down($db) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='pbcms-safe-mode'");
            if ($res->num_rows == 0 || intval($res->fetch_assoc()['value']) === 0) {
                $db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='pbcms-safe-mode'");
            } else {
                echo "Non-default value for policy \"pbcms-safe-mode\" was found, not deleting the entry." . PHP_EOL;
            }
        }
    }