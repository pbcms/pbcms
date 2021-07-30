<?php
    /**
     * Template: pb-portal
     * Authors: The PBCMS developers
     * Description: This template is used for so-called "portal" pages. The signin and signup pages can be seen as portal pages.
     * 
     * ==== REQUIRED DATA ATTRIBUTES ====
     * - title;         The title of the page. " - PBCMS" will be put behind the title.
     * - description;   The description of the page. Used for meta generation.
     * - copyright;     The copyright message placed at the bottom of the page.
     */

     use Library\Meta;
     $meta = new Meta;
     $meta->set('robots', 'index, nofollow');
     $meta->set('title', $data['title']);
     $meta->set('description', $data['description']);
     if (isset($data['meta'])) $meta->batch($data['meta']);
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <?php echo $meta->generate(); ?>

        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Didact+Gothic&display=swap" rel="stylesheet">
        <style><?php echo file_get_contents(PUBFILES_DIR . '/css/pbcms-system-pages.css');?></style>
        <style><?php echo file_get_contents(PUBFILES_DIR . '/css/pbcms-portal-pages.css');?></style>
    </head>
    <body>
        <form class="pbcms-system-display portal" action="/pb-api/auth/create-session" method="post">
            <div class="top-bar">
                <div class="logo-container">
                    <img src="<?php echo SITE_LOCATION; ?>/pb-pubfiles/img/pb-logos/full-dark.png" alt="PBCMS Logo (Full, Dark)">
                </div>
                <h1 class="portal-subtitle">
                    <?php echo $data['subtitle']; ?>
                </h1>
            </div>
            <div class="content">
                <?php echo $content; ?>
            </div>
        </form>

        <div class="page-copyright">
            <p>
                <a href="<?php echo (isset($_GET['referrer']) ? $_GET['referrer'] : SITE_LOCATION); ?>">Back to site</a> - <?php echo $data['copyright']; ?>, Powered by <a href="https://pbcms.io" target="_blank">PBCMS</a>.
            </p>
        </div>
    </body>
</html>