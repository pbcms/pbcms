<?php
    use Library\Language;

    $lang = new Language();
    $lang->detectLanguage();
    $lang->load();

    $data = array(
        "title" => $lang->get('error-pages.error-404.title', "Page not found"),
        "description" => $lang->get('error-pages.error-404.description', "The requested page does not exist.")
    );
?>

<p>
    <?php echo $lang->get('error-pages.error-404.description', "The requested page does not exist."); ?>
</p>