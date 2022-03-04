<?php
    /**
     * Reason for this class is to make sure that the system sidebar items are the first a user will see. 
     * The Dashboard registry will check if this class has been initialized before, otherwise it will let this class register it's sidebar items first with a special code called the "permit".
     */

    namespace Source;

    use Registry\Dashboard;
    use Library\Language;

    class DashboardSidebar {
        private static $isReady = false;

        public static function initialized() {
            return self::$isReady;
        }

        public static function initialize($permit) {
            if (self::$isReady) return false;
            $lang = new Language;
            $lang->detectLanguage();
            $lang->load();

            $sidebarItems = array(
                [
                    "title" => $lang->get('templates.pb-dashboard.section-titles.overview', "Overview"),
                    "section" => "overview",
                    "icon" => "disc",
                    "category" => "no_category"
                ],
                [
                    "title" => $lang->get('templates.pb-dashboard.section-titles.updates', "Updates"),
                    "section" => "updates",
                    "icon" => "refresh-cw",
                    "permissions" => ["site.administration.perform-updates", "modules.update"],
                    "category" => "no_category"
                ],
                [
                    "title" => $lang->get('templates.pb-dashboard.section-titles.media', "Media"),
                    "section" => "media",
                    "icon" => "image",
                    "category" => "content"
                ],
                [
                    "title" => $lang->get('templates.pb-dashboard.section-titles.virtual-paths', "Virtual paths"),
                    "section" => "virtual-paths",
                    "icon" => "list",
                    "permissions" => ["router.virtual-path.list"],
                    "category" => "content"
                ],
                [
                    "title" => $lang->get('templates.pb-dashboard.section-titles.profile', "Profile"),
                    "section" => "profile",
                    "icon" => "user",
                    "category" => "configuration"
                ],
                [
                    "title" => $lang->get('templates.pb-dashboard.section-titles.users', "Users"),
                    "section" => "users",
                    "icon" => "users",
                    "permissions" => ["user.list"],
                    "category" => "configuration"
                ],
                [
                    "title" => $lang->get('templates.pb-dashboard.section-titles.modules', "Modules"),
                    "section" => "modules",
                    "icon" => "package",
                    "permissions" => ["modules.list"],
                    "category" => "configuration"
                ],
                [
                    "title" => $lang->get('templates.pb-dashboard.section-titles.roles', "Roles"),
                    "section" => "roles",
                    "icon" => "folder",
                    "permissions" => ["role.list"],
                    "category" => "configuration"
                ],
                [
                    "title" => $lang->get('templates.pb-dashboard.section-titles.permissions', "Permissions"),
                    "section" => "permissions",
                    "icon" => "shield",
                    "permissions" => ["permission.list"],
                    "category" => "configuration"
                ],
                [
                    "title" => $lang->get('templates.pb-dashboard.section-titles.policies', "Policies"),
                    "section" => "policies",
                    "icon" => "book",
                    "permissions" => ["policy.list"],
                    "category" => "configuration"
                ]
            );

            foreach($sidebarItems as $item) {
                $item['permit'] = $permit;
                Dashboard::register($item['section'], $item);
            }

            self::$isReady = true;
            return true;
        }
    }
    
