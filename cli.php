#!/usr/bin/php
<?php
    define("OPERATION_MODE", "CLI");
    require_once(__DIR__ . '/app/Loader.php');

    function core_initialized() {
        $arguments = array_slice($_SERVER['argv'], 1);
        $cli = new \Library\Cli;
        $cli->process($arguments);
    }