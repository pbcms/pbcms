<?php
    namespace Command;

    use Library\Cli;

    class Session {
        public function execute() {
            $cli = new Cli();
            $cli->printLine();
            $cli->printLine("  Not yet available.");
            $cli->printLine();
        }
    }