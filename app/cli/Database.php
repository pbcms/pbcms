<?php
    namespace Command;

    use \Library\DatabaseMigrator as Migrator;
    use \Library\Cli;

    class Database {
        public function execute($arg) {
            $this->arg = $arg;
            $cli = new Cli;

            $cli->printLine();

            if (isset($this->arg->arguments['help'])) {
                $this->showHelp();
                $cli->printLine();
                return;
            }

            switch($this->arg->details) {
                case "migrate":
                    
                    $migrator = new Migrator;
                    $migrator->migrate();
                    break;
                case "rollback":
                    $options = $this->getOptions();
                    $migrator = new Migrator;

                    if (count((array) $options) < 1) {
                        $cli->printLine("No options were provided. Use --help to list options.");
                        break;
                    }

                    $earliest = $migrator->listMigrations($options)[0];
                    if ($earliest) {
                        $cli->printLine("Do you really want to rollback until the following migration?");
                        $cli->printLine("  " . $earliest->migration);
                        $cli->printLine();
                        $prompt = $cli->prompt("Yes / no ~> ");
                        $cli->printLine();
                        print_r($prompt);
                        if (strtolower($prompt) == "yes") {
                            $migrator->rollback($options);
                        } else {
                            $cli->printLine("I did not recieve a clear \"yes\", cancelling.");
                        }
                    } else {
                        $migrator->rollback($options);
                    }
                    break;
                case "list":
                    $options = $this->getOptions();
                    $migrator = new Migrator;
                    $list = $migrator->listMigrations($options);

                    $longest = (object) array(
                        "id" => 4,
                        "version" => 9,
                        "task" => 6,
                        "name" => 6
                    );

                    foreach($list as $item) {
                        if (strlen(strval($item->id)) > $longest->id) $longest->id = strlen(strval($item->id)) + 2;
                        if (strlen(strval($item->version)) > $longest->version) $longest->version = strlen(strval($item->version)) + 2;
                        if (strlen(strval($item->task)) > $longest->task) $longest->task = strlen(strval($item->task)) + 2;
                        if (strlen(strval($item->name)) > $longest->name) $longest->name = strlen(strval($item->name)) + 2;
                    }

                    function createColumn($value, $width) {
                        return "  " . $value . join('', array_fill(0, $width - strlen(strval($value)), ' '));
                    }

                    $cli->printLine(str_replace(' ', '=', join("+", array(
                        "",
                        createColumn("=", $longest->id),
                        createColumn("=", $longest->version),
                        createColumn("=", $longest->task),
                        createColumn("=", $longest->name),
                        ""
                    ))));

                    $cli->printLine(join("|", array(
                        "",
                        createColumn("ID", $longest->id),
                        createColumn("Version", $longest->version),
                        createColumn("Task", $longest->task),
                        createColumn("Name", $longest->name),
                        ""
                    )));

                    $cli->printLine(str_replace(' ', '=', join("+", array(
                        "",
                        createColumn("=", $longest->id),
                        createColumn("=", $longest->version),
                        createColumn("=", $longest->task),
                        createColumn("=", $longest->name),
                        ""
                    ))));

                    foreach($list as $item) {
                        $cli->printLine(join("|", array(
                            "",
                            createColumn($item->id, $longest->id),
                            createColumn($item->version, $longest->version),
                            createColumn($item->task, $longest->task),
                            createColumn($item->name, $longest->name),
                            ""
                        )));
    
                        $cli->printLine(str_replace(' ', '-', join("+", array(
                            "",
                            createColumn("-", $longest->id),
                            createColumn("-", $longest->version),
                            createColumn("-", $longest->task),
                            createColumn("-", $longest->name),
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
            if (isset($this->arg->flags['m'])) $options['migration'] = $this->arg->flags['m'];
            if (isset($this->arg->flags['v'])) $options['version'] = $this->arg->flags['v'];
            if (isset($this->arg->flags['t'])) $options['task'] = $this->arg->flags['t'];
            if (isset($this->arg->flags['n'])) $options['name'] = $this->arg->flags['n'];
                    
            if (isset($this->arg->arguments['id'])) $options['id'] = $this->arg->arguments['id'];
            if (isset($this->arg->arguments['migration'])) $options['migration'] = $this->arg->arguments['migration'];
            if (isset($this->arg->arguments['version'])) $options['version'] = $this->arg->arguments['version'];
            if (isset($this->arg->arguments['task'])) $options['task'] = $this->arg->arguments['task'];
            if (isset($this->arg->arguments['name'])) $options['name'] = $this->arg->arguments['name'];
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