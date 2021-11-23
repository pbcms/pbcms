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
            $list = (array) $cli->list();
            $cmds = array_keys($list);
            usort($cmds, function($a,$b) { return strlen($b)-strlen($a); });
            $length = strlen($cmds[0]);

            foreach($list as $cmd => $desc) {
                $cli->printLine("\e[96m" . $cmd . "\e[97m:" . str_repeat(' ', 2 + $length - strlen($cmd)) . ($desc ? $desc : "\e[90mNo description.\e[97m"));
            }

            $cli->printLine();
        }
    }