<?php
    namespace DatabaseMigrator;

    class AddModuleRepositories__1__0_0_9 {
        public function up($db, $log) {
            $repositories = array(
                array("Stable", "https://raw.githubusercontent.com/pbcms/updates/main/modules/stable.json", 1),
                array("Beta", "https://raw.githubusercontent.com/pbcms/updates/main/modules/beta.json", 0),
                array("Unstable", "https://raw.githubusercontent.com/pbcms/updates/main/modules/unstable.json", 0)
            );

            foreach($repositories as $repository) {
                $this->validateRepository($db, $repository[0], $repository[1], $repository[2], $log);
            }
        }

        public function down($db, $log) {
            $log("No need to remove the module repository objects.");
        }

        public function validateRepository($db, $name, $url, $enabled, $log) {
            $repo = $db->query("SELECT `id` FROM `" . DATABASE_TABLE_PREFIX . "objects` WHERE `type`='modules-repository' AND `name`='${name}'");
            if ($repo->num_rows > 0) {
                $log("Object for ${name} repository already exists.");
                $repo = $repo->fetch_assoc()['id'];
            } else {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "objects` (`type`, `name`) VALUES ('modules-repository', '${name}')");
                $repo = $db->query("SELECT `id` FROM `" . DATABASE_TABLE_PREFIX . "objects` WHERE `type`='modules-repository' AND `name`='${name}'");
                if ($repo->num_rows > 0) {
                    $repo = $repo->fetch_assoc()['id'];
                } else {
                    $log("Failed to create object for repository ${name}, please add manually.");
                    return;
                }
            }

            $propUrl = $db->query("SELECT `id` FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `object`='${repo}' AND `property`='url'");
            if ($propUrl->num_rows > 0) {
                $log("Url property for ${name} repository already exists.");
            } else {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "object-properties` (`object`, `property`, `value`) VALUES ('${repo}', 'url', '${url}')");
                $propUrl = $db->query("SELECT `id` FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `object`='${repo}' AND `property`='url'");
                if ($propUrl->num_rows == 0) {
                    $log("Failed to create url property for repository ${name}, please add manually.");
                }
            }

            $propEnabled = $db->query("SELECT `id` FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `object`='${repo}' AND `property`='enabled'");
            if ($propEnabled->num_rows > 0) {
                $log("Enabled property for ${name} repository already exists.");
            } else {
                $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "object-properties` (`object`, `property`, `value`) VALUES ('${repo}', 'enabled', '${enabled}')");
                $propEnabled = $db->query("SELECT `id` FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `object`='${repo}' AND `property`='enabled'");
                if ($propEnabled->num_rows == 0) {
                    $log("Failed to create enabled property for repository ${name}, please add manually.");
                }
            }
        }
    }