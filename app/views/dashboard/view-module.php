<?php
    use Library\ModuleConfig;

    $module = $data['module'];
    $config = new ModuleConfig($module->module);
    $configTableContent = '';
    foreach($config->properties() as $property => $value) {
        $item = (object) array(
            "name" => $property,
            "value" => $value,
            "type" => "string"
        );

        if (is_numeric($value)) $item->type = "number";
        $configTableContent .= renderRow($item);
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

    function renderRow($item) {
        $result = '<tr config-property="' . $item->name . '"><td id="config-property">' . $item->name . '</td><td>';
        switch($item->type) {
            case 'string':
                $result .= '<input type="text" name="' . $item->name . '" value="' . $item->value . '" placeholder="Enter a value">';
                break;
            case 'number':
                $result .= '<input type="number" name="' . $item->name . '" value="' . $item->value . '" placeholder="Enter a value">';
                break;
        }

        $result .= "</td></tr>";
        return $result;
    }
?>

<section class="page-introduction">
    <h1>
        <?php echo getParameter($module, 'name', $module->module); ?>
    </h1>
    <p>
        <?php echo getParameter($module, 'description', 'No description'); ?>
    </p>
</section>

<section class="actions transparent no-padding">
    <a enable-module="<?php echo $module->module; ?>">
        Enable
    </a>
    <a disable-module="<?php echo $module->module; ?>">
        Disable
    </a>
    <a href="<?php echo SITE_LOCATION; ?>pb-dashboard/module-config/<?php echo $module->module; ?>">
        Open configurator
    </a>
</section>

<section class="module-config no-padding transparent">
    <table>
        <thead>
            <tr>
                <th>
                    Name
                </th>
                <th>
                    Value
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                echo $configTableContent;
            ?>
        </tbody>
    </table>
</section>