<?php
    namespace DatabaseMigrator;

    class AddTokenDefaultExpirationPolicy__4__0_0_2 {
        public function up($db) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='token-default-expiration'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('token-default-expiration', 86400)");
            } else {
                echo "Entry for policy \"token-default-expiration\" already exists, not overriding it." . PHP_EOL;
            }
        }

        public function down($db) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='token-default-expiration'");
            if ($res->num_rows == 0 || intval($res->fetch_assoc()['value']) === 86400) {
                $db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='token-default-expiration'");
            } else {
                echo "Non-default value for policy \"token-default-expiration\" was found, not deleting the entry." . PHP_EOL;
            }
        }
    }