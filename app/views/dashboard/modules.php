<?php
    use Library\Modules;

    $modules = new Modules;

    $modlist = $modules->list('all');

    $tableContent = '';
    foreach($modlist as $mod) {
        $tableContent .= renderRow((object) array(
            "name" => $modules->prepareFunctionNaming($mod),
            "enabled" => $modules->enabled($mod),
            "preCore" => $modules->preCore($mod),
            "loaded" => $modules->isLoaded($mod)
        ));
    }

    function renderRow($item) {
        $result = '<tr policy-name="' . $item->name . '"><td>' . $item->name . '</td><td>' . ($item->enabled ? "yes" : "no") . '</td><td>' . ($item->preCore ? "yes" : "no") . '</td><td>' . ($item->loaded ? "yes" : "no") . '</td></td></tr>';
        return $result;
    }
?>

<section class="page-introduction">
    <h1>
        <?php echo $this->lang->get("pages.pb-dashboard.modules.page-title", "Manage modules"); ?>
    </h1>
    <p>
        <?php echo $this->lang->get("pages.pb-dashboard.modules.page-subtitle", "Manage or install modules for your website."); ?>
    </p>
</section>

<section class="no-padding">
    <table>
        <thead>
            <th>
                <?php echo $this->lang->get("pages.pb-dashboard.modules.table.column-name", "Name"); ?>
            </th>
            <th>
                <?php echo $this->lang->get("pages.pb-dashboard.modules.table.column-enabled", "Enabled"); ?>
            </th>
            <th>
                <?php echo $this->lang->get("pages.pb-dashboard.modules.table.column-pre-core", "Pre-Core"); ?>
            </th>
            <th>
                <?php echo $this->lang->get("pages.pb-dashboard.modules.table.column-is-loaded", "Is loaded?"); ?>
            </th>
        </thead>
        <tbody>
            <?php
                echo $tableContent;
            ?>
        </tbody>
    </table>
</section>

<section class="transparent no-padding buttons">
    
</section>