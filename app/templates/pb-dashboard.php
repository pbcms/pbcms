<?php
    /**
     * Template: pb-dashboard
     * Authors: The PBCMS developers
     * Description: This template is used for dashboard pages.
     * 
     * ==== REQUIRED DATA ATTRIBUTES ====
     * - title;         The title of the page. "Dashboard - " will be appended before the given title.
     * - section;       The section in the sidebar to be activated.
     *  
     * ==== OPTIONAL DATA ATTRIBUTES ====
     * - meta;          Additional meta tags to be included by the Meta Batch definition.
     * - head;          Additional head assets to be included by the Assets Batch function definition. (eg. styles)
     * - body;          Additional body assets to be included by the Assets Batch function definition. (eg. scripts)
     */

    use Library\Meta;
    use Library\Users;
    use Library\UserPermissions;

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

    $assets->registerBody('script', "feather.replace();");

    if (isset($data['head'])) $assets->registerBatch('head', $data['head']);
    if (isset($data['body'])) $assets->registerBatch('body', $data['body']);

    $users = new Users;
    $userperm = new UserPermissions;
    $shortcuts = json_decode($users->metaGet($this->session->user->id, 'dashboard-shortcuts'));
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
                    <img src="<?php echo SITE_LOCATION; ?>pb-pubfiles/img/pb-logos/full-dark.png" alt="PBCMS Logo (Full, Dark)">
                </div>

                <div class="sidebar-options">
                    <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/overview" <?php if ($data['section'] == 'overview') echo 'active'; ?>>
                        <i data-feather="disc"></i>
                        <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.overview', "Overview"); ?></span>
                    </a>
                    
                    <?php if ($userperm->check($this->session->user->id, "site.administration.perform-updates")) { ?>
                        <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/updates" <?php if ($data['section'] == 'updates') echo 'active'; ?>>
                            <i data-feather="refresh-cw"></i>
                            <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.updates', "Updates"); ?></span>
                        </a>
                    <?php } ?>

                    <h6 class="category">
                        <?php echo $this->lang->get('templates.pb-dashboard.section-categories.content'); ?>
                    </h6>
                    <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/media" <?php if ($data['section'] == 'media') echo 'active'; ?>>
                        <i data-feather="image"></i>
                        <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.media', "Media"); ?></span>
                    </a>
                    <?php if ($userperm->check($this->session->user->id, "router.virtual-path.%")) { ?>
                        <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/virtual-paths"  <?php if ($data['section'] == 'virtual-paths') echo 'active'; ?>>
                            <i data-feather="list"></i>
                            <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.virtual-paths', "Virtual paths"); ?></span>
                        </a>
                    <?php } ?>

                    <h6 class="category">
                        <?php echo $this->lang->get('templates.pb-dashboard.section-categories.configuration'); ?>
                    </h6>
                    <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/profile" <?php if ($data['section'] == 'profile') echo 'active'; ?>>
                        <i data-feather="user"></i>
                        <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.profile', "Profile"); ?></span>
                    </a>
                    <?php if ($userperm->check($this->session->user->id, "user.%.other")) { ?>
                        <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/users" <?php if ($data['section'] == 'users') echo 'active'; ?>>
                            <i data-feather="users"></i>
                            <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.users', "Users"); ?></span>
                        </a>
                    <?php } ?>
                    <?php if ($userperm->check($this->session->user->id, "module.%")) { ?>
                        <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/modules" <?php if ($data['section'] == 'modules') echo 'active'; ?>>
                            <i data-feather="package"></i>
                            <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.modules', "Modules"); ?></span>
                        </a>
                    <?php } ?>
                    <?php if ($userperm->check($this->session->user->id, "role.%")) { ?>
                        <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/roles" <?php if ($data['section'] == 'roles') echo 'active'; ?>>
                            <i data-feather="folder"></i>
                            <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.roles', "Roles"); ?></span>
                        </a>
                    <?php } ?>
                    <?php if ($userperm->check($this->session->user->id, "permission.%")) { ?>
                        <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/permissions" <?php if ($data['section'] == 'permissions') echo 'active'; ?>>
                            <i data-feather="shield"></i>
                            <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.permissions', "Permissions"); ?></span>
                        </a>
                    <?php } ?>
                    <?php if ($userperm->check($this->session->user->id, "object.%")) { ?>
                        <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/objects" <?php if ($data['section'] == 'objects') echo 'active'; ?>>
                            <i data-feather="box"></i>
                            <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.objects', "Objects"); ?></span>
                        </a>
                    <?php } ?>
                    <?php if ($userperm->check($this->session->user->id, "policy.%")) { ?>
                        <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/policies" <?php if ($data['section'] == 'policies') echo 'active'; ?>>
                            <i data-feather="book"></i>
                            <span><?php echo $this->lang->get('templates.pb-dashboard.section-titles.policies', "Policies"); ?></span>
                        </a>
                    <?php } ?>



                    <h6 class="category">
                        <?php echo $this->lang->get('templates.pb-dashboard.section-categories.shortcuts', "Shortcuts"); ?> - <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/shortcuts"><?php echo $this->lang->get('common.words.edit', "Edit"); ?></a>
                    </h6>
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
                            help
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
                            content
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-container">
                <div class="content-shadow"></div>
                <div class="content">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>

        <?php
            echo $assets->generateBody();
        ?>
    </body>
</html>