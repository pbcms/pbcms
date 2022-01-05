<?php
    use Library\Language;

    $lang = new Language();
    $lang->detectLanguage();
    $lang->load();

    $data = array(
        "title" => $lang->get('error-pages.error-403.title', "Forbidden access"),
        "description" => $lang->get('error-pages.error-403.description', "You do not have access to the requested page.")
    );
?>

<p>
    <?php echo $lang->get('error-pages.error-403.description', "You do not have access to the requested page."); ?>
</p>