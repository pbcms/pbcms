<?php
    namespace Command;

    use Library\Cli;

    class Ping {
        public function execute() {
            $cli = new Cli();
            $cli->printLine();
            $cli->printLine("  Pong!");
            $cli->printLine();
        }
    }