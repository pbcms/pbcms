<?php
    namespace DatabaseMigrator;

    class MedtypeProfilepictureMaxsize__2__0_0_11 {
        public function up($db) {
            $res = $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "media-type` SET `max-size`='2MB' WHERE `type`='profilepicture'");
        }

        public function down($db) {
            $res = $db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "media-type` SET `max-size`='2M' WHERE `type`='profilepicture'");
        }
    }