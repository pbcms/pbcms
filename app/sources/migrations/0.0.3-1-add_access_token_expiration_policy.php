<?php
    namespace DatabaseMigrator;

    class AddAccessTokenExpirationPolicy__1__0_0_3 {
        public function up($db) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='access-token-expiration'");
            if ($res->num_rows == 0) {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('access-token-expiration', 3600)");
            } else {
                echo "Entry for policy \"access-token-expiration\" already exists, not overriding it." . PHP_EOL;
            }
        }

        public function down($db) {
            $res = $db->query("SELECT `value` FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='access-token-expiration'");
            if ($res->num_rows == 0 || intval($res->fetch_assoc()['value']) === 3600) {
                $db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='access-token-expiration'");
            } else {
                echo "Non-default value for policy \"access-token-expiration\" was found, not deleting the entry." . PHP_EOL;
            }
        }
    }