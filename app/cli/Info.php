<?php
    namespace Command;

    use Library\Cli;

    class Info {
        public function execute() {
            $cli = new Cli;
            $cli->printLine();

            $cli->printLine("+============================================================+");
            $cli->printLine("|                                                            |");
            $cli->printLine("|  \e[97mWelcome to the interactive \e[94mPBCMS\e[97m command line interface!  |");
            $cli->printLine("|  You can use the \e[96mhelp\e[97m command to get a list of commands.   |");
            $cli->printLine("|                                                            |");
            $cli->printLine("+============================================================+");

            $cli->printLine();
        }
    }