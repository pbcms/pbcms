Overview

<?php
    $l = new \Library\Language();
    $l->detectLanguage();
    $l->load();
    var_dump($l->current());