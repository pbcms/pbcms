<?php
    namespace Command;

    use \Library\Cli;
    use \Library\Policy as PolicyWorker;

    class Policy {
        public function execute($arg) {
            $this->arg = $arg;
            $cli = new Cli;
            $policy = new PolicyWorker;
            $options = $this->getOptions();

            $cli->printLine();

            if (isset($this->arg->arguments['help'])) {
                $this->showHelp();
                $cli->printLine();
                return;
            }

            switch($this->arg->details) {
                case "get":
                    if (isset($options['name'])) {
                        $res = $policy->get($options['name']);
                        if (!$res) {
                            $cli->printLine("Policy \"\e[90m" . $options['name'] . "\e[39m\" does not exist.");
                        } else {
                            $cli->printLine("  Value: " . $res);
                        }
                    } else {
                        $cli->printLine("Missing \e[96m--name\e[39m or \e[96m-n\e[39m option.");
                    }
                    
                    break;
                case "exists":
                    if (isset($options['name'])) {
                        $res = $policy->exists($options['name']);
                        if (!$res) {
                            $cli->printLine("\e[91m[-]\e[39m Policy \"\e[90m" . $options['name'] . "\e[39m\" does not exist.");
                        } else {
                            $cli->printLine("\e[92m[+]\e[39m Policy \"\e[90m" . $options['name'] . "\e[39m\" exists.");
                        }
                    } else {
                        $cli->printLine("Missing \e[96m--name\e[39m or \e[96m-n\e[39m option.");
                    }

                    break;
                case "set": 

                    break;
                case "delete": 

                    break;
                case "list":
                    $list = $policy->list(-1);

                    $longest = (object) array(
                        "id" => 4,
                        "name" => 6,
                        "value" => 7
                    );

                    foreach($list as $item) {
                        $item = (object) $item;
                        if (strlen(strval($item->id)) > $longest->id) $longest->id = strlen(strval($item->id)) + 2;
                        if (strlen(strval($item->name)) > $longest->name) $longest->name = strlen(strval($item->name)) + 2;
                        if (strlen(strval($item->value)) > $longest->value) $longest->value = strlen(strval($item->value)) + 2;
                    }

                    function createColumn($value, $width) {
                        return "  " . $value . join('', array_fill(0, $width - strlen(strval($value)), ' '));
                    }

                    $cli->printLine(str_replace(' ', '=', join("+", array(
                        "",
                        createColumn("=", $longest->id),
                        createColumn("=", $longest->name),
                        createColumn("=", $longest->value),
                        ""
                    ))));

                    $cli->printLine(join("|", array(
                        "",
                        createColumn("ID", $longest->id),
                        createColumn("Name", $longest->name),
                        createColumn("Value", $longest->value),
                        ""
                    )));

                    $cli->printLine(str_replace(' ', '=', join("+", array(
                        "",
                        createColumn("=", $longest->id),
                        createColumn("=", $longest->name),
                        createColumn("=", $longest->value),
                        ""
                    ))));

                    foreach($list as $item) {
                        $item = (object) $item;
                        $cli->printLine(join("|", array(
                            "",
                            createColumn($item->id, $longest->id),
                            createColumn($item->name, $longest->name),
                            createColumn($item->value, $longest->value),
                            ""
                        )));
    
                        $cli->printLine(str_replace(' ', '-', join("+", array(
                            "",
                            createColumn("-", $longest->id),
                            createColumn("-", $longest->name),
                            createColumn("-", $longest->value),
                            ""
                        ))));
                    }

                    break;
                case "help":
                    $this->showHelp();
                    break;
                default:
                    if (isset($this->arg->arguments['help'])) {
                        $this->showHelp();
                    } else {
                        $cli->printLine("No action defined! Try with --help");
                    }
            }

            $cli->printLine();
        }

        public function getOptions() {
            $options = array();
            if (isset($this->arg->flags['i'])) $options['id'] = $this->arg->flags['i'];
            if (isset($this->arg->flags['n'])) $options['name'] = $this->arg->flags['n'];
            if (isset($this->arg->flags['v'])) $options['value'] = $this->arg->flags['v'];
                    
            if (isset($this->arg->arguments['id'])) $options['id'] = $this->arg->arguments['id'];
            if (isset($this->arg->arguments['name'])) $options['name'] = $this->arg->arguments['name'];
            if (isset($this->arg->arguments['value'])) $options['value'] = $this->arg->arguments['value'];
            return $options;
        }

        public function showHelp() {
            $cli = new Cli;
            $cli->printLine("Usage: database [\e[92maction\e[39m] [\e[96moptions\e[39m]");

            $cli->printLine();

            $cli->printLine("Actions: ");
            $cli->printLine();
            $cli->printLine("  \e[92mmigrate\e[39m                 Apply all eligable migrations that haven't been applied yet.");
            $cli->printLine("  \e[92mrollback\e[39m [\e[96moptions\e[39m]      Rollback changes up to a defined point in time.");
            $cli->printLine("  \e[92mlist\e[39m [\e[96moptions\e[39m]          List migration based on criteria.");

            $cli->printLine();

            $cli->printLine("Options: ");
            $cli->printLine();
            $cli->printLine("  \e[96m--id\e[39m, \e[96m-i\e[39m                The ID of the targeted migration: \e[90mx\e[39m");
            $cli->printLine("  \e[96m--migration\e[39m, \e[96m-m\e[39m         The name of the targeted migration: \e[90mx.x.x_x_name-of-migration\e[39m");
            $cli->printLine("  \e[96m--version\e[39m, \e[96m-v\e[39m           The version of the targeted migration: \e[90mx.x.x\e[39m");
            $cli->printLine("  \e[96m--task\e[39m, \e[96m-t\e[39m              The task ID of the targeted migration (\e[93mNOT UNIQUE!\e[39m): \e[90mx\e[39m");
            $cli->printLine("  \e[96m--name\e[39m, \e[96m-n\e[39m              The name of the targeted migration.\e[90mname-of-migration\e[39m");
        }
    }