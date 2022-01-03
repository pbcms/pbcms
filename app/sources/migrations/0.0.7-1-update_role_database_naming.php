<?php
    namespace DatabaseMigrator;

    class UpdateRoleDatabaseNaming__1__0_0_7 {
        public function up($db, $log) {
            $resultInPermissionsTable = $db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "permissions` WHERE `target` LIKE 'grp:%'");
            foreach($resultInPermissionsTable->fetch_all(MYSQLI_ASSOC) as $item) {
                $item = (array) $item;
                $itemId = $item['id'];
                $newTarget = "role:" . explode(':', $item['target'])[1];
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "permissions` SET `target`='${newTarget}' WHERE `id`='${itemId}'");
            }

            $resultInRelationsTableWhereOrigin = $db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "relations` WHERE `type` LIKE 'group:%'");
            foreach($resultInRelationsTableWhereOrigin->fetch_all(MYSQLI_ASSOC) as $item) {
                $item = (array) $item;
                $itemId = $item['id'];
                $newType = "role:" . explode(':', $item['type'])[1];
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "relations` SET `type`='${newType}' WHERE `id`='${itemId}'");
            }

            $resultInRelationsTableWhereTarget = $db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "relations` WHERE `type` LIKE '%:group'");
            foreach($resultInRelationsTableWhereTarget->fetch_all(MYSQLI_ASSOC) as $item) {
                $item = (array) $item;
                $itemId = $item['id'];
                $newType = explode(':', $item['type'])[0] . ":role";
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "relations` SET `type`='${newType}' WHERE `id`='${itemId}'");
            }
        }

        public function down($db, $log) {
            $resultInPermissionsTable = $db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "permissions` WHERE `target` LIKE 'role:%'");
            foreach($resultInPermissionsTable->fetch_all(MYSQLI_ASSOC) as $item) {
                $item = (array) $item;
                $itemId = $item['id'];
                $newTarget = "grp:" . explode(':', $item['target'])[1];
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "permissions` SET `target`='${newTarget}' WHERE `id`='${itemId}'");
            }

            $resultInRelationsTableWhereOrigin = $db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "relations` WHERE `type` LIKE 'role:%'");
            foreach($resultInRelationsTableWhereOrigin->fetch_all(MYSQLI_ASSOC) as $item) {
                $item = (array) $item;
                $itemId = $item['id'];
                $newType = "group:" . explode(':', $item['type'])[1];
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "relations` SET `type`='${newType}' WHERE `id`='${itemId}'");
            }

            $resultInRelationsTableWhereTarget = $db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "relations` WHERE `type` LIKE '%:role'");
            foreach($resultInRelationsTableWhereTarget->fetch_all(MYSQLI_ASSOC) as $item) {
                $item = (array) $item;
                $itemId = $item['id'];
                $newType = explode(':', $item['type'])[0] . ":group";
                $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "relations` SET `type`='${newType}' WHERE `id`='${itemId}'");
            }
        }
    }