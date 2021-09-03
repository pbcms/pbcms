<?php
    namespace Command;

    use Library\Cli;

    class Module {
        public function execute() {
            $cli = new Cli();
            $cli->printLine();
            $cli->printLine("  Not yet available.");
            $cli->printLine();
        }
    }