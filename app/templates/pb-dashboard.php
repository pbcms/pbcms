<?php
    /**
     * Template: pb-dashboard
     * Authors: The PBCMS developers
     * Description: This template is used for dashboard pages.
     * 
     * ==== REQUIRED DATA ATTRIBUTES ====
     * - title;             The title of the page. "Dashboard - " will be appended before the given title.
     * - section;           The section in the sidebar to be activated.
     *  
     * ==== OPTIONAL DATA ATTRIBUTES ====
     * - meta;              Additional meta tags to be included by the Meta Batch definition.
     * - head;              Additional head assets to be included by the Assets Batch function definition. (eg. styles)
     * - body;              Additional body assets to be included by the Assets Batch function definition. (eg. scripts)
     * - backup_section;    Backup section if section defined in section does not exist.
     */

    use Library\Meta;
    use Library\Users;
    use Library\UserPermissions;
    use Library\Policy;
    use Registry\Event;
    use Registry\Dashboard;
    use Helper\Header;

    $meta = new Meta;
    $meta->set('robots', 'index, nofollow');
    $meta->set('title', $this->lang->get("templates.pb-dashboard.dashboard", "Dashboard") . ' - ' . $data['title']);
    if (isset($data['meta'])) $meta->batch($data['meta']);

    Core::SystemAssets();
    $assets = new \Library\Assets;
    $assets->registerHead('style', 'https://fonts.googleapis.com', array("rel" => "preconnect"));
    $assets->registerHead('style', 'https://fonts.gstatic.com', array("rel" => "preconnect", "properties" => "crossorigin"));
    $assets->registerHead('style', 'https://fonts.googleapis.com/css2?family=Didact+Gothic&display=swap');
    $assets->registerHead('script', 'https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js');
    $assets->registerHead('style', "pbcms-dashboard.css", array("origin" => "pubfiles"));

    $assets->registerBody('script', "pbcms-dashboard.js", array("origin" => "pubfiles"));
    $assets->registerBody('script', "feather.replace();");

    if (isset($data['head'])) $assets->registerBatch('head', $data['head']);
    if (isset($data['body'])) $assets->registerBatch('body', $data['body']);

    $users = new Users;
    $userperm = new UserPermissions;
    $shortcuts = json_decode($users->metaGet($this->session->user->id, 'dashboard-shortcuts'));
    $userModel = $this->__model("user");
    $user = $userModel->info();

    if (isset($data['section'])) {
        $sectionDetails = Dashboard::get($data['section']);
        if ($sectionDetails && isset($sectionDetails['permissions'])) {
            $passed = false;
            foreach($sectionDetails['permissions'] as $permission) if ($userModel->check($permission)) $passed = true;
            if (!$passed) {
                Header::Location(SITE_LOCATION . 'pb-dashboard/overview');
                die();
            }
        }
    }

    $policy = new Policy;
    $supportLocation = $policy->get('support-location');
    if (!$supportLocation) $supportLocation = 'https://support.pbcms.io/';
    $docsLocation = $policy->get('docs-location');
    if (!$docsLocation) $docsLocation = 'https://docs.pbcms.io/';

    if (!isset($data['backup_section'])) $data['backup_section'] = null;

    $activeSection = $data['section'];
    $backupSection = $data['backup_section'];

    $parseCategoryItems = function($category = null) use ($userModel, $activeSection, $backupSection) {
        $result = "";

        foreach(Dashboard::list($category) as $section => $item) {
            if (isset($item['permissions'])) {
                $passed = false;
                foreach($item['permissions'] as $permission) {
                    if ($userModel->check($permission)) $passed = true;
                }

                if (!$passed) continue;
            }

            if (!isset($item['url'])) $item['url'] = $section;
            $active = ($activeSection == $section ? " active" : "");
            $backup = ($backupSection == $section ? " backup-active" : "");

            $result .= '<a href="' . SITE_LOCATION . 'pb-dashboard/' . $item['url'] . '"' . $active . $backup . ">";
            $result .= '<i data-feather="' . $item['icon'] . '"></i>';
            $result .= '<span>' . $item['title'] . '</span>';
            $result .= '</a>';
        }

        return $result;
    };

    $printCategory = function($name, $category = null) use ($parseCategoryItems) {
        $items = $parseCategoryItems($category);
        if (!empty($items)) {
            echo '<h6 class="category">' . $name . '</h6>';
            echo $items;
        }
    };
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <?php 
            echo $meta->generate(); 
            echo $assets->generateHead();
        ?>
    </head>
    <body>
        <div class="modal-container">
            <!-- <div class="modal"></div> -->
        </div>

        <div class="sidebar">
            <div class="sidebar-inner">
                <?php
                    if (Core::Safemode()) {
                        ?>
                            <b style="color:red; position:absolute;text-align:center;width:300px;">IN SAFE MODE</b>
                        <?php
                    }
                ?>

                <div class="sidebar-top-branding">
                    <img src="<?=SITE_LOCATION?>pb-pubfiles/img/pb-logos/full-dark.png" alt="PBCMS Logo (Full, Dark)">
                </div>

                <div class="sidebar-options">
                    <?=$parseCategoryItems("no_category")?>
                    <?=$printCategory($this->lang->get('templates.pb-dashboard.section-categories.content', 'content'), "content")?>
                    <?=$printCategory($this->lang->get('templates.pb-dashboard.section-categories.configuration', 'configuration'), "configuration")?>

                    <h6 class="category"><?php echo $this->lang->get('templates.pb-dashboard.section-categories.shortcuts', "shortcuts"); ?> - <a href="<?=SITE_LOCATION?>pb-dashboard/shortcuts"><?php echo $this->lang->get('common.words.edit', "Edit"); ?></a></h6>
                    <?php
                        if (!$shortcuts || count($shortcuts) < 1) {
                            ?>
                                <a href="/pb-dashboard/shortcuts/create" <?php if ($data['section'] == 'shortcuts') echo 'active'; ?>>
                                    <i data-feather="link"></i>
                                    <span>New shortcut</span>
                                </a>
                            <?php
                        } else {
                            foreach($shortcuts as $shortcut) {
                                $shortcut = (array) $shortcut;
                                switch($shortcut['shortcut-type']) {
                                    case 'module-config':
                                        ?>
                                            <a href="<?php echo SITE_LOCATION . 'pb-dashboard/module-config/' . $shortcut['target']; ?>" <?php if ($data['section'] == 'module-config-' . $shortcut['target']) echo 'active'; ?>>
                                                <i data-feather="<?php echo $shortcut['icon']; ?>"></i>
                                                <span><?php echo $shortcut['title']; ?></span>
                                            </a>
                                        <?php
                                        break;
                                    case 'custom': 
                                        ?>
                                            <a href="<?php echo SITE_LOCATION . $shortcut['target']; ?>">
                                                <i data-feather="<?php echo $shortcut['icon']; ?>"></i>
                                                <span><?php echo $shortcut['title']; ?></span>
                                            </a>
                                        <?php
                                        break;
                                    case 'remote':
                                        ?>
                                            <a href="<?php echo $shortcut['target']; ?>">
                                                <i data-feather="<?php echo $shortcut['icon']; ?>"></i>
                                                <span><?php echo $shortcut['title']; ?></span>
                                            </a>
                                        <?php
                                        break;
                                }
                            }
                        }
                    ?>

                    <?=$printCategory($this->lang->get('templates.pb-dashboard.section-categories.other', 'other'), "other")?>
                </div>
                <div class="sidebar-footer">
                    <p>&copy; <a href="https://pbcms.io" target="_blank">PBCMS Project</a> <?php echo date("Y"); ?></p>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="control-bar">
                <div class="control-button search-control">
                    <input type="text" class="search-bar" name="search-bar" placeholder="<?php echo $this->lang->get('templates.pb-dashboard.top-bar.search-placeholder', "Start typing to search."); ?>">
                    <div class="search-button">
                        <i data-feather="search" class="icon-button"></i>
                    </div>
                </div>
                <div class="control-button help-control">
                    <input type="checkbox" id="help-control" name="account-controls" hidden>
                    <label for="help-control" class="icon-button">
                        <i data-feather="help-circle"></i>
                    </label>

                    <div class="control-menu">
                        <div class="inner-menu">
                            <div class="header">
                                <p>
                                    Help & Support
                                </p>
                            </div>
                            <div class="items">
                                <div class="item">
                                    <a href="<?php echo $supportLocation; ?>" target="_blank">
                                        Support
                                    </a>
                                </div>
                                <div class="item">
                                    <a href="<?php echo $docsLocation; ?>" target="_blank">
                                        Documentation
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="control-button exit-dashboard">
                    <div class="icon-button">
                        <i data-feather="home"></i>
                    </div>
                </div>
                <div class="control-button account-control">
                    <input type="checkbox" id="account-control" name="account-controls" hidden>
                    <label for="account-control" class="icon-button">
                        <i data-feather="user"></i>
                    </label>

                    <div class="control-menu">
                        <div class="inner-menu">
                            <div class="header">
                                <p class="user-fullname">
                                    <?php echo $user->firstname . ' ' . $user->lastname; ?>
                                </p>
                            </div>
                            <div class="items">
                                <div class="item">
                                    <a href="<?=SITE_LOCATION?>pb-dashboard/profile">
                                        Profile
                                    </a>
                                </div>
                                <div class="item">
                                    <a href="<?=SITE_LOCATION?>pb-auth/signout">
                                        Signout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-container">
                <div class="content-shadow"></div>
                <div class="content">
                    <?=$content?>
                </div>
            </div>
        </div>

        <?php
            echo $assets->generateBody();
        ?>
    </body>
</html>