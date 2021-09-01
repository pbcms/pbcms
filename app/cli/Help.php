<?php
    namespace Command;

    use Library\Cli;

    class Help {
        public function execute() {
            $cli = new Cli;
            $cli->printLine();

            $cli->printLine("\e[94mPBCMS\e[97m Help center.");

            $cli->printLine();
            
            $cli = new \Library\Cli;
            $cmds = $cli->list();
            foreach($cmds as $cmd => $desc) {
                $cli->printLine("\e[96m" . $cmd . "\e[97m: " . ($desc ? $desc : "\e[90mNo description.\e[97m"));
            }

            $cli->printLine();
        }
    }