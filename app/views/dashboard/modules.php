<?php
    use Library\Modules;
    use Library\ModuleManager;

    $modules = new Modules;
    $modman = new ModuleManager;

    $modlist = $modules->list('all');

    $tableContent = '';
    foreach($modlist as $mod) {
        $tableContent .= renderRow((object) $modman->moduleSummary($mod));
    }

    function renderRow($item) {
        $result =   '<tr module-name="' . $item->module . '">';
        $result .=      '<td>' . getParameter($item, 'name', $item->module) . '</td>';
        $result .=      '<td>' . limitLength(getParameter($item, 'description', 'No description'), 100) . '</td>';
        $result .=      '<td>' . getParameter($item, 'author') . '</td>';
        $result .=      '<td>' . getParameter($item, 'version') . '</td>';
        $result .=      '<td>' . ($item->repo ? 'Yes' : 'No') . '</td>';
        $result .=      '<td>' . ($item->enabled ? "Yes" : "No") . '</td>';
        $result .=      '<td><a href="' . SITE_LOCATION . 'pb-dashboard/modules/' . $item->module . '">Manage</a></td>';
        $result .=      '<td>' . ($item->configuratorAvailable ? '<a href="' . SITE_LOCATION . 'pb-dashboard/module-config/' . $item->module . '">Open</a>' : '') . '</td>';
        $result .=  '</tr>';
        return $result;
    }

    function getParameter($item, $param, $alternative = null) {
        $local = ($item->local && isset(((array) $item->local)[$param]) ? ((array) $item->local)[$param] : null);
        $repo = ($item->repo && isset(((array) $item->repo)[$param]) ? ((array) $item->repo)[$param] : null);
        return ($local ? $local : ($repo ? $repo : $alternative));
    }

    function limitLength($string, $maxsize = 100) {
        if (strlen($string) > $maxsize) {
            $stringCut = substr($string, 0, $maxsize);
            $endPoint = strrpos($stringCut, ' ');

            $string = $endPoint? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
            $string .= ' ...';
        }

        return $string;
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

<section class="actions transparent no-padding">
    <a show-available-modules>
        Install new module
    </a>
</section>

<section class="table-modules no-padding transparent">
    <table class="installed-modules">
        <thead>
            <th>
                <?php echo $this->lang->get("pages.pb-dashboard.modules.table.column-name", "Name"); ?>
            </th>
            <th>
                Description
            </th>
            <th>
                Author
            </th>
            <th>
                Version
            </th>
            <th>
                In Repository
            </th>
            <th>
                <?php echo $this->lang->get("pages.pb-dashboard.modules.table.column-enabled", "Enabled"); ?>
            </th>
            <th>
                Manage
            </th>
            <th>
                Configurator
            </th>
        </thead>
        <tbody>
            <?php
                echo $tableContent;
            ?>
        </tbody>
    </table>
    <table class="available-modules">
        <thead>
            <th>
                <?php echo $this->lang->get("pages.pb-dashboard.modules.table.column-name", "Name"); ?>
            </th>
            <th>
                Description
            </th>
            <th>
                Author
            </th>
            <th>
                Version
            </th>
            <th>
                License
            </th>
            <th>
                Actions
            </th>
        </thead>
        <tbody>
            
        </tbody>
    </table>
</section>