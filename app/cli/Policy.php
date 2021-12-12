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
                            $cli->printLine("Policy \"\e[96m" . $options['name'] . "\e[39m\" does not exist.");
                        } else {
                            $cli->printLine("  Value: \"\e[96m" . $res . "\e[39m\".");
                        }
                    } else {
                        $cli->printLine("Missing \e[96m--name\e[39m or \e[96m-n\e[39m option.");
                    }
                    
                    break;
                case "exists":
                    if (isset($options['name'])) {
                        $res = $policy->exists($options['name']);
                        if (!$res) {
                            $cli->printLine("\e[91m[-]\e[39m Policy \"\e[96m" . $options['name'] . "\e[39m\" does not exist.");
                        } else {
                            $cli->printLine("\e[92m[+]\e[39m Policy \"\e[96m" . $options['name'] . "\e[39m\" exists.");
                        }
                    } else {
                        $cli->printLine("Missing \e[96m--name\e[39m or \e[96m-n\e[39m option.");
                    }

                    break;
                case "set": 
                    if (isset($options['name']) && isset($options['value'])) {
                        $res = $policy->set($options['name'], $options['value']);
                        $cli->printLine("Value of policy \"\e[96m" . $options['name'] . "\e[39m\" was set to \"\e[96m" . $options['value'] . "\e[39m\".");
                    } else {
                        if (!isset($options['name'])) $cli->printLine("Missing \e[96m--name\e[39m or \e[96m-n\e[39m option.");
                        if (!isset($options['value'])) $cli->printLine("Missing \e[96m--value\e[39m or \e[96m-v\e[39m option.");
                    }

                    break;
                case "delete": 
                    if (isset($options['name'])) {
                        if (!isset($options['confirm'])) {
                            $cli->printLine("Are you sure that your want to delete the \"\e[96m" . $options['name'] . "\e[39m\" policy?");
                            $prompt = $cli->prompt("Type \"\e[92mconfirm\e[39m\" to continue: \e[92m");
                            $cli->printLine("\e[39m ");
                            if (strtolower($prompt) != "confirm") {
                                $cli->printLine("Did not receive \"\e[92mconfirm\e[39m\", cancelling.");
                                break;
                            }
                        }

                        $res = $policy->delete($options['name']);
                        $cli->printLine("Policy \"\e[96m" . $options['name'] . "\e[39m\" was deleted.");
                    } else {
                        if (!isset($options['name'])) $cli->printLine("Missing \e[96m--name\e[39m or \e[96m-n\e[39m option.");
                        if (!isset($options['value'])) $cli->printLine("Missing \e[96m--value\e[39m or \e[96m-v\e[39m option.");
                    }

                    break;
                case "list":
                    if (isset($options['limit']) && isset($options['offset'])) {
                        $list = $policy->list($options['limit'], $options['offset']);
                    } else if (isset($options['limit'])) {
                        $list = $policy->list($options['limit']);
                    } else if (isset($options['offset'])) {
                        $list = $policy->list(-1, $options['offset']);
                    } else {
                        $list = $policy->list(-1);
                    }

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

                    $cli->printLine(str_replace(' ', '=', join("+", array(
                        "",
                        $this->createColumn("=", $longest->id),
                        $this->createColumn("=", $longest->name),
                        $this->createColumn("=", $longest->value),
                        ""
                    ))));

                    $cli->printLine(join("|", array(
                        "",
                        $this->createColumn("ID", $longest->id),
                        $this->createColumn("Name", $longest->name),
                        $this->createColumn("Value", $longest->value),
                        ""
                    )));

                    $cli->printLine(str_replace(' ', '=', join("+", array(
                        "",
                        $this->createColumn("=", $longest->id),
                        $this->createColumn("=", $longest->name),
                        $this->createColumn("=", $longest->value),
                        ""
                    ))));

                    foreach($list as $item) {
                        $item = (object) $item;
                        $cli->printLine(join("|", array(
                            "",
                            $this->createColumn($item->id, $longest->id),
                            $this->createColumn($item->name, $longest->name),
                            $this->createColumn($item->value, $longest->value),
                            ""
                        )));
    
                        $cli->printLine(str_replace(' ', '-', join("+", array(
                            "",
                            $this->createColumn("-", $longest->id),
                            $this->createColumn("-", $longest->name),
                            $this->createColumn("-", $longest->value),
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
            if (isset($this->arg->flags['n'])) $options['name'] = $this->arg->flags['n'];
            if (isset($this->arg->flags['v'])) $options['value'] = $this->arg->flags['v'];
            if (isset($this->arg->flags['l'])) $options['limit'] = $this->arg->flags['l'];
            if (isset($this->arg->flags['o'])) $options['offset'] = $this->arg->flags['o'];
                    
            if (isset($this->arg->arguments['name'])) $options['name'] = $this->arg->arguments['name'];
            if (isset($this->arg->arguments['value'])) $options['value'] = $this->arg->arguments['value'];
            if (isset($this->arg->arguments['limit'])) $options['limit'] = $this->arg->arguments['limit'];
            if (isset($this->arg->arguments['offset'])) $options['offset'] = $this->arg->arguments['offset'];
            if (isset($this->arg->arguments['confirm'])) $options['confirm'] = $this->arg->arguments['confirm'];
            return $options;
        }

        public function showHelp() {
            $cli = new Cli;
            $cli->printLine("Usage: policy [\e[92maction\e[39m] [\e[96moptions\e[39m]");

            $cli->printLine();

            $cli->printLine("Actions: ");
            $cli->printLine();
            $cli->printLine("  \e[92mlist\e[39m                    List all current policies.");
            $cli->printLine("  \e[92mset\e[39m [\e[96moptions\e[39m]           Set the value of a policy.");
            $cli->printLine("  \e[92mget\e[39m [\e[96moptions\e[39m]           Get the value of a policy.");
            $cli->printLine("  \e[92mexists\e[39m [\e[96moptions\e[39m]        Check if a policy exists.");
            $cli->printLine("  \e[92mdelete\e[39m [\e[96moptions\e[39m]        Delete a policy.");

            $cli->printLine();

            $cli->printLine("Options: ");
            $cli->printLine();
            $cli->printLine("  \e[96m--name\e[39m, \e[96m-n\e[39m              The name of the policy.                 \e[90mname-of-policy\e[39m");
            $cli->printLine("  \e[96m--value\e[39m, \e[96m-v\e[39m             The value of the migration:             \e[90mexample-value\e[39m");
            $cli->printLine("  \e[96m--limit\e[39m, \e[96m-l\e[39m             Limit X amount of result:               \e[90mx\e[39m");
            $cli->printLine("  \e[96m--offset\e[39m, \e[96m-o\e[39m            Skip the first amount of results by X:  \e[90mx\e[39m");
            $cli->printLine("  \e[96m--confirm\e[39m               Confirm an action.");
        }

        public function createColumn($value, $width) {
            return "  " . $value . join('', array_fill(0, $width - strlen(strval($value)), ' '));
        }
    }