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
    $meta = new Meta;
    $meta->set('robots', 'index, nofollow');
    $meta->set('title', 'Dashboard - ' . $data['title']);
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
                    <img src="<?php echo SITE_LOCATION; ?>/pb-pubfiles/img/pb-logos/full-dark.png" alt="PBCMS Logo (Full, Dark)">
                </div>

                <div class="sidebar-options">
                    <a href="/pb-dashboard/overview" section="overview">
                        <i data-feather="disc"></i>
                        <span>Overview</span>
                    </a>
                    <a href="/pb-dashboard/updates" section="updates">
                        <i data-feather="refresh-cw"></i>
                        <span>Updates</span>
                    </a>

                    <h6 class="category">
                        Content
                    </h6>
                    <a href="/pb-dashboard/media" section="media">
                        <i data-feather="image"></i>
                        <span>Media</span>
                    </a>
                    <a href="/pb-dashboard/virtual-paths" section="virtual-paths">
                        <i data-feather="list"></i>
                        <span>Virtual paths</span>
                    </a>

                    <h6 class="category">
                        Configuration
                    </h6>
                    <a href="/pb-dashboard/users" section="users">
                        <i data-feather="users"></i>
                        <span>Users</span>
                    </a>
                    <a href="/pb-dashboard/modules" section="modules">
                        <i data-feather="package"></i>
                        <span>Modules</span>
                    </a>
                    <a href="/pb-dashboard/objects" section="objects">
                        <i data-feather="box"></i>
                        <span>Objects</span>
                    </a>
                    <a href="/pb-dashboard/policies" section="policies">
                        <i data-feather="book"></i>
                        <span>Policies</span>
                    </a>

                    <h6 class="category">
                        Shortcuts - <a href="/pb-dashboard/shortcuts">edit</a>
                    </h6>
                    <a href="/pb-dashboard/module-config/maintenance" section="shortcut-maintenance">
                        <i data-feather="cloud-off"></i>
                        <span>Maintenance</span>
                    </a>
                    <a href="/pb-dashboard/module-config/articles" section="articles">
                        <i data-feather="file-text"></i>
                        <span>Articles</span>
                    </a>
                </div>
                <div class="sidebar-footer">
                    <p>&copy; <a href="https://pbcms.io" target="_blank">PBCMS Project</a> <?php echo date("Y"); ?></p>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="control-bar">
                <div class="control-button search-control">
                    <input type="text" class="search-bar" name="search-bar" placeholder="Start typing to search.">
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