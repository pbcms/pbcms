<?php
    use Library\Language;

    $lang = new Language();
    $lang->detectLanguage();
    $lang->load();

    $data = array(
        "title" => $lang->get('error-pages.error-welcome-page.title', 'Welcome'),
        "description" => $lang->get('error-pages.error-welcome-page.description', 'Welcome to your new PBCMS site!'),
        "subtitle" => $lang->get('error-pages.error-welcome-page.description', 'Welcome to your new PBCMS site!'),
    );
?>

<p>
    <?php echo str_replace("{{SITE_LOCATION}}", SITE_LOCATION, $lang->get('error-pages.error-welcome-page.content-1', "Welcome to your new PCBMS instance, this is a small introduction page. <br>Start by <a href='{{SITE_LOCATION}}pb-auth/signin'>signin in to your account</a> and by exploring the dashboard!")); ?>
</p>

<i>
    <?php echo $lang->get('error-pages.error-welcome-page.content-2', "By the way, this page is only shown because it is enabled by default when no homepage exist. You can <a href='#'>disable it</a> or <a href='#'>create a homepage</a>.") ?>
</i>