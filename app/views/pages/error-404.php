<?php
    use Library\Language;

    $lang = new Language();
    $lang->detectLanguage();
    $lang->load();

    $data = array(
        "title" => $lang->get('error-pages.error-404.title'),
        "description" => $lang->get('error-pages.error-404.description')
    )
?>

<p>
    <?php echo $lang->get('error-pages.error-404.description'); ?>
</p>