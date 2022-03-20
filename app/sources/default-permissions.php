<?php
    /**
     * Reason for this class is to make sure that the system permissions will be suggested before custom permissions. 
     * The PermissionHints registry will check if this class has been initialized before, otherwise it will let this class register it's sidebar items first with a special code called the "permit".
     */

    namespace Source;

    use Registry\PermissionHints;
    use Library\Language;

    class DefaultPermissions {
        private static $isReady = false;

        public static function initialized() {
            return self::$isReady;
        }

        public static function initialize($permit) {
            if (self::$isReady) return false;
            $lang = new Language;
            $lang->detectLanguage();
            $lang->load();

            $descriptions = (array) $lang->get('permission-hints');
            $permissions = array(
                "site.migrate-database",
                "site.core.update",


                "media.get.%",
                "media.delete.%",
                "media.transfer.%",
                "media.info.%",
                "media.list.others",


                "object.create",
                "object.exists",
                "object.info",
                "object.list",
                "object.properties",
                "object.purge",
                "object.get-property",
                "object.property-exists",
                "object.set-property",
                "object.delete-property",


                "policy.set",
                "policy.get",
                "policy.exists",
                "policy.delete",
                "policy.list",


                "relation.create",
                "relation.delete",
                "relation.find",
                "relation.list",


                "role.create",
                "role.find",
                "role.list",
                "role.update",
                "role.delete",
                "role.get-id",


                "user.create",
                "user.info",
                "user.list",
                "user.update",
                "user.delete",
                "user.get-id",


                "router.virtual-path.create",
                "router.virtual-path.delete",
                "router.virtual-path.find",
                "router.virtual-path.list",


                "permission.grant",
                "permission.reject",
                "permission.clear",
                "permission.check",
                "permission.list",
                "permission.find",
                "permission.hints",

                
                "modules.list",
                "modules.exists",
                "modules.installed",
                "modules.enable",
                "modules.disable",
                "modules.install",
                "modules.summary",
                "modules.update",
                "modules.remove",
                "modules.info",
                "modules.add-repository",
                "modules.enable-repository",
                "modules.disable-repository",
                "modules.remove-repository",
                "modules.get-repository",
                "modules.list-repositories",
                "modules.repository-info",
                "modules.refresh-repository",
                "modules.refresh-repositories",
            );

            foreach($permissions as $perm) {
                PermissionHints::register($perm, (isset($descriptions[$perm]) ? $descriptions[$perm] : null), $permit);
            }

            self::$isReady = true;
            return true;
        }
    }
    

