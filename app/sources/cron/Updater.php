<?php
    namespace Cron;

    use Library\Cli;
    use Library\ModuleManager;

    class Updater {
        public function execute($options) {
            $cli = new Cli;
            $modman = new ModuleManager;
            if ($options['verbose']) $cli->printLine("Refreshing enabled repositories.");
            $modman->refreshRepositories();
        }
    }