<?php
    use Library\Language;

    $lang = new Language();
    $lang->detectLanguage();
    $lang->load();

    $data = array(
        "title" => $lang->get('error-pages.error-500.title', "Internal server error"),
        "description" => $lang->get('error-pages.error-500.description', "An internal server error occured.")
    );
?>

<p>
    <?php echo $lang->get('error-pages.error-500.description', "An internal server error occured."); ?>
</p>