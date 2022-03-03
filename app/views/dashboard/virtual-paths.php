<?php
    use Library\VirtualPath;

    $vPaths = new VirtualPath;
    $vPathList = $vPaths->list();
    $tableContent = '';

    foreach($vPathList as $vPath) {
        $tableContent .= renderRow((object) $vPath);
    }

    function renderRow($item) {
        $result = '<tr id="' . $item->id. '"><td>' . $item->id . '</td><td>' . $item->path . '</td><td>' . $item->target . '</td><td>' . $item->lang . '</td></tr>';
        return $result;
    }
?>

<section class="page-introduction">
    <h1>
        Virtual paths
    </h1>
    <p>
        View virtual paths on your website.
    </p>
</section>

<section class="no-margin overflow-scroll transparent">
    <table>
        <thead>
            <th class="smaller">
                ID
            </th>
            <th>
                Path
            </th>
            <th>
                Target
            </th>
            <th>
                Language
            </th>
        </thead>
        <tbody>
            <?php
                echo $tableContent;
            ?>
        </tbody>
    </table>
</section>