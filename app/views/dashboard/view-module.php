<?php
    $module = $data['module'];

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
        <?php echo getParameter($module, 'name', $module->module); ?>
    </h1>
    <p>
    <?php echo getParameter($module, 'description', 'No description'); ?>
    </p>
</section>

<section>
    cool
</section>