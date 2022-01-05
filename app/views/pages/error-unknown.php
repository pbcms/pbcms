<?php
    use Library\Language;

    $lang = new Language();
    $lang->detectLanguage();
    $lang->load();

    $data = array(
        "title" => "Error " . $error,
        "description" => "Error " . $error
    );
?>

<p>
    <?php echo "Error " . $error; ?>
</p>