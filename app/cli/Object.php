<?php
    namespace Command;

    use \Library\Objects;
    use \Library\Cli;

    class _Object {
        public function execute($arg) {
            $this->arg = $arg;
            $cli = new Cli;
            $objectManager = new Objects;

            $cli->printLine();

            if (isset($this->arg->arguments['help'])) {
                $this->showHelp();
                $cli->printLine();
                return;
            }

            switch($this->arg->details) {
                case "create":
                    $options = $this->getOptions();
                    if (!isset($options['type']))   return $cli->printLine("Missing the object type flag! Define it using the \e[96m--type\e[39m or \e[96m-t\e[39m flag." . PHP_EOL);
                    if (empty($options['type']))    return $cli->printLine("The object type flag is empty!" . PHP_EOL);
                    if (!isset($options['object'])) return $cli->printLine("Missing the object name flag! Define it using the \e[96m--object\e[39m or \e[96m-o\e[39m flag." . PHP_EOL);
                    if (empty($options['object']))  return $cli->printLine("The object name flag is empty!" . PHP_EOL);

                    if ($objectManager->create($options['type'], $options['object'])) {
                        $cli->printLine("The object has been created!");
                    } else {
                        $cli->printLine("An object with name \"" . $options['object'] . "\" and type \"" . $options['type'] . "\" already exists!");
                    }

                    break;
                case "exists":
                    $options = $this->getOptions();
                    if (isset($options['id'])) {
                        if (empty($options['id']))  return $cli->printLine("The object id flag is empty! Alternatively, use the \e[96m--type\e[39m (\e[96m-t\e[39m) and \e[96m--object\e[39m (\e[96m-o\e[39m) flags" . PHP_EOL);
                        $result = $objectManager->exists($options['id']);
                    } else {
                        if (!isset($options['type']))   return $cli->printLine("Missing the object type flag! Define it using the \e[96m--type\e[39m or \e[96m-t\e[39m flag." . PHP_EOL);
                        if (empty($options['type']))    return $cli->printLine("The object type flag is empty!" . PHP_EOL);
                        if (!isset($options['object'])) return $cli->printLine("Missing the object name flag! Define it using the \e[96m--object\e[39m or \e[96m-o\e[39m flag." . PHP_EOL);
                        if (empty($options['object']))  return $cli->printLine("The object name flag is empty!" . PHP_EOL);
                        $result = $objectManager->exists($options['type'], $options['object']);
                    }

                    if ($result) {
                        $cli->printLine("This object exists.");
                    } else {
                        $cli->printLine("This object does not exist.");
                    }

                    break;
                case "info":
                    $options = $this->getOptions();
                    if (isset($options['id'])) {
                        if (empty($options['id']))  return $cli->printLine("The object id flag is empty! Alternatively, use the \e[96m--type\e[39m (\e[96m-t\e[39m) and \e[96m--object\e[39m (\e[96m-o\e[39m) flags" . PHP_EOL);
                        $result = $objectManager->info($options['id']);
                    } else {
                        if (!isset($options['type']))   return $cli->printLine("Missing the object type flag! Define it using the \e[96m--type\e[39m or \e[96m-t\e[39m flag." . PHP_EOL);
                        if (empty($options['type']))    return $cli->printLine("The object type flag is empty!" . PHP_EOL);
                        if (!isset($options['object'])) return $cli->printLine("Missing the object name flag! Define it using the \e[96m--object\e[39m or \e[96m-o\e[39m flag." . PHP_EOL);
                        if (empty($options['object']))  return $cli->printLine("The object name flag is empty!" . PHP_EOL);
                        $result = $objectManager->info($options['type'], $options['object']);
                    }

                    if ($result) {
                        $cli->printLine("  ID:         \e[90m" . $result->id . "\e[39m");
                        $cli->printLine("  Type:       \e[90m" . $result->type . "\e[39m");
                        $cli->printLine("  Name:       \e[90m" . $result->name . "\e[39m");
                        $cli->printLine("  Created:    \e[90m" . $result->created . "\e[39m");
                        $cli->printLine("  Updated:    \e[90m" . $result->updated . "\e[39m");
                    } else {
                        $cli->printLine("Unknown object!");
                    }

                    break;
                case "list":
                    $options = $this->getOptions();
                    $limit = (isset($options['limit']) ? $options['limit'] : 10);
                    $offset = (isset($options['offset']) ? $options['offset'] : null);

                    if (isset($options['type'])) {
                        $list = $objectManager->list($options['type'], $limit, $offset);
                    } else {
                        $list = $objectManager->list($limit, $offset);
                    }

                    $longest = (object) array(
                        "id" => 4,
                        "type" => 6,
                        "name" => 6,
                        "created" => 9,
                        "updated" => 9
                    );

                    foreach($list as $item) {
                        $item = (object) $item;
                        if (strlen(strval($item->id)) > $longest->id) $longest->id = strlen(strval($item->id)) + 2;
                        if (strlen(strval($item->type)) > $longest->type) $longest->type = strlen(strval($item->type)) + 2;
                        if (strlen(strval($item->name)) > $longest->name) $longest->name = strlen(strval($item->name)) + 2;
                        if (strlen(strval($item->created)) > $longest->created) $longest->created = strlen(strval($item->created)) + 2;
                        if (strlen(strval($item->updated)) > $longest->updated) $longest->updated = strlen(strval($item->updated)) + 2;
                    }

                    $cli->printLine(str_replace(' ', '=', join("+", array(
                        "",
                        $this->createColumn("=", $longest->id),
                        $this->createColumn("=", $longest->type),
                        $this->createColumn("=", $longest->name),
                        $this->createColumn("=", $longest->created),
                        $this->createColumn("=", $longest->updated),
                        ""
                    ))));

                    $cli->printLine(join("|", array(
                        "",
                        $this->createColumn("ID", $longest->id),
                        $this->createColumn("Type", $longest->type),
                        $this->createColumn("Name", $longest->name),
                        $this->createColumn("Created", $longest->created),
                        $this->createColumn("Updated", $longest->updated),
                        ""
                    )));

                    $cli->printLine(str_replace(' ', '=', join("+", array(
                        "",
                        $this->createColumn("=", $longest->id),
                        $this->createColumn("=", $longest->type),
                        $this->createColumn("=", $longest->name),
                        $this->createColumn("=", $longest->created),
                        $this->createColumn("=", $longest->updated),
                        ""
                    ))));

                    foreach($list as $item) {
                        $item = (object) $item;
                        $cli->printLine(join("|", array(
                            "",
                            $this->createColumn($item->id, $longest->id),
                            $this->createColumn($item->type, $longest->type),
                            $this->createColumn($item->name, $longest->name),
                            $this->createColumn($item->created, $longest->created),
                            $this->createColumn($item->updated, $longest->updated),
                            ""
                        )));
    
                        $cli->printLine(str_replace(' ', '-', join("+", array(
                            "",
                            $this->createColumn("-", $longest->id),
                            $this->createColumn("-", $longest->type),
                            $this->createColumn("-", $longest->name),
                            $this->createColumn("-", $longest->created),
                            $this->createColumn("-", $longest->updated),
                            ""
                        ))));
                    }

                    break;
                case "properties":
                    $options = $this->getOptions();

                    if (isset($options['id'])) {
                        if (empty($options['id']))  return $cli->printLine("The object id flag is empty! Alternatively, use the \e[96m--type\e[39m (\e[96m-t\e[39m) and \e[96m--object\e[39m (\e[96m-o\e[39m) flags" . PHP_EOL);
                        $list = $objectManager->properties($options['id']);
                    } else {
                        if (!isset($options['type']))   return $cli->printLine("Missing the object type flag! Define it using the \e[96m--type\e[39m or \e[96m-t\e[39m flag." . PHP_EOL);
                        if (empty($options['type']))    return $cli->printLine("The object type flag is empty!" . PHP_EOL);
                        if (!isset($options['object'])) return $cli->printLine("Missing the object name flag! Define it using the \e[96m--object\e[39m or \e[96m-o\e[39m flag." . PHP_EOL);
                        if (empty($options['object']))  return $cli->printLine("The object name flag is empty!" . PHP_EOL);
                        $list = $objectManager->properties($options['type'], $options['object']);
                    }

                    if (!$list) {
                        $cli->printLine("Unknown object or no properties!");
                    } else {
                        $longest = (object) array(
                            "id" => 4,
                            "property" => 10,
                            "value" => 7,
                        );

                        foreach($list as $item) {
                            $item = (object) $item;
                            if (strlen(strval($item->id)) > $longest->id) $longest->id = strlen(strval($item->id)) + 2;
                            if (strlen(strval($item->property)) > $longest->property) $longest->property = strlen(strval($item->property)) + 2;
                            if (strlen(strval($item->value)) > $longest->value) $longest->value = strlen(strval($item->value)) + 2;
                        }

                        $cli->printLine(str_replace(' ', '=', join("+", array(
                            "",
                            $this->createColumn("=", $longest->id),
                            $this->createColumn("=", $longest->property),
                            $this->createColumn("=", $longest->value),
                            ""
                        ))));

                        $cli->printLine(join("|", array(
                            "",
                            $this->createColumn("ID", $longest->id),
                            $this->createColumn("Property", $longest->property),
                            $this->createColumn("Value", $longest->value),
                            ""
                        )));

                        $cli->printLine(str_replace(' ', '=', join("+", array(
                            "",
                            $this->createColumn("=", $longest->id),
                            $this->createColumn("=", $longest->property),
                            $this->createColumn("=", $longest->value),
                            ""
                        ))));

                        foreach($list as $item) {
                            $item = (object) $item;
                            $cli->printLine(join("|", array(
                                "",
                                $this->createColumn($item->id, $longest->id),
                                $this->createColumn($item->property, $longest->property),
                                $this->createColumn($item->value, $longest->value),
                                ""
                            )));
        
                            $cli->printLine(str_replace(' ', '-', join("+", array(
                                "",
                                $this->createColumn("-", $longest->id),
                                $this->createColumn("-", $longest->property),
                                $this->createColumn("-", $longest->value),
                                ""
                            ))));
                        }
                    }

                    break;
                case "purge":
                    $options = $this->getOptions();
                    if (isset($options['id'])) {
                        if (empty($options['id']))  return $cli->printLine("The object id flag is empty! Alternatively, use the \e[96m--type\e[39m (\e[96m-t\e[39m) and \e[96m--object\e[39m (\e[96m-o\e[39m) flags" . PHP_EOL);
                        $result = $objectManager->exists($options['id']);
                    } else {
                        if (!isset($options['type']))   return $cli->printLine("Missing the object type flag! Define it using the \e[96m--type\e[39m or \e[96m-t\e[39m flag." . PHP_EOL);
                        if (empty($options['type']))    return $cli->printLine("The object type flag is empty!" . PHP_EOL);
                        if (!isset($options['object'])) return $cli->printLine("Missing the object name flag! Define it using the \e[96m--object\e[39m or \e[96m-o\e[39m flag." . PHP_EOL);
                        if (empty($options['object']))  return $cli->printLine("The object name flag is empty!" . PHP_EOL);
                        $result = $objectManager->exists($options['type'], $options['object']);
                    }

                    if ($result) {
                        if (!isset($options['confirm'])) {
                            if (isset($options['id'])) {
                                $cli->printLine("Are you sure that your want to purge the object with id \"\e[96m" . $options['id'] . "\e[39m\"?");
                            } else {
                                $cli->printLine("Are you sure that your want to purge the object with type \"\e[96m" . $options['type'] . "\e[39m\" and name \"\e[96m" . $options['object'] . "\e[39m\"?");
                            }

                            $prompt = $cli->prompt("Type \"\e[92mconfirm\e[39m\" to continue: \e[92m");
                            $cli->printLine("\e[39m ");
                            if (strtolower($prompt) != "confirm") {
                                $cli->printLine("Did not receive \"\e[92mconfirm\e[39m\", cancelling.");
                                break;
                            }
                        }

                        if (isset($options['id'])) {
                            $res = $objectManager->purge($options['id']);
                            $cli->printLine("Object with id \"\e[96m" . $options['id'] . "\e[39m\" was deleted.");
                        } else {
                            $res = $objectManager->purge($options['type'], $options['object']);
                            $cli->printLine("Object with type \"\e[96m" . $options['type'] . "\e[39m\" and name \"\e[96m" . $options['object'] . "\e[39m\" was deleted.");
                        }
                    } else {
                        $cli->printLine("This object does not exist.");
                    }

                    break;
                case "get":
                    $options = $this->getOptions();
                    if (isset($options['id'])) {
                        if (empty($options['id']))  return $cli->printLine("The object id flag is empty! Alternatively, use the \e[96m--type\e[39m (\e[96m-t\e[39m) and \e[96m--object\e[39m (\e[96m-o\e[39m) flags" . PHP_EOL);
                        $result = $objectManager->exists($options['id']);
                    } else {
                        if (!isset($options['type']))   return $cli->printLine("Missing the object type flag! Define it using the \e[96m--type\e[39m or \e[96m-t\e[39m flag." . PHP_EOL);
                        if (empty($options['type']))    return $cli->printLine("The object type flag is empty!" . PHP_EOL);
                        if (!isset($options['object'])) return $cli->printLine("Missing the object name flag! Define it using the \e[96m--object\e[39m or \e[96m-o\e[39m flag." . PHP_EOL);
                        if (empty($options['object']))  return $cli->printLine("The object name flag is empty!" . PHP_EOL);
                        $result = $objectManager->exists($options['type'], $options['object']);
                    }

                    if (!isset($options['property'])) return $cli->printLine("Missing the property flag! Define it using the \e[96m--property\e[39m or \e[96m-p\e[39m flag." . PHP_EOL);
                    if (empty($options['property']))  return $cli->printLine("The property flag is empty!" . PHP_EOL);

                    if ($result) {
                        if (isset($options['id'])) {
                            $result = $objectManager->get($options['id'], $options['property']);
                            if (!$result) return $cli->printLine("Property \"\e[96m" . $options['property'] . "\e[39m\" does not exist on object with id \"\e[96m" . $options['id'] . "\e[39m\"." . PHP_EOL);
                        } else {
                            $result = $objectManager->get($options['type'], $options['object'], $options['property']);
                            if (!$result) return $cli->printLine("Property \"\e[96m" . $options['property'] . "\e[39m\" does not exist on object with type \"\e[96m" . $options['type'] . "\e[39m\" and name \"\e[96m" . $options['object'] . "\e[39m\"." . PHP_EOL);
                        }

                        $cli->printLine("Value is: \e[96m" . $result . "\e[39m");
                    } else {
                        $cli->printLine("This object does not exist.");
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
            if (isset($this->arg->flags['t'])) $options['type'] = $this->arg->flags['t'];
            if (isset($this->arg->flags['o'])) $options['object'] = $this->arg->flags['o'];
            if (isset($this->arg->flags['p'])) $options['property'] = $this->arg->flags['p'];
            if (isset($this->arg->flags['v'])) $options['value'] = $this->arg->flags['v'];
                    
            if (isset($this->arg->arguments['id'])) $options['id'] = $this->arg->arguments['id'];
            if (isset($this->arg->arguments['type'])) $options['type'] = $this->arg->arguments['type'];
            if (isset($this->arg->arguments['object'])) $options['object'] = $this->arg->arguments['object'];
            if (isset($this->arg->arguments['property'])) $options['property'] = $this->arg->arguments['property'];
            if (isset($this->arg->arguments['value'])) $options['value'] = $this->arg->arguments['value'];
            if (isset($this->arg->arguments['limit'])) $options['limit'] = $this->arg->arguments['limit'];
            if (isset($this->arg->arguments['offset'])) $options['offset'] = $this->arg->arguments['offset'];
            if (isset($this->arg->arguments['confirm'])) $options['confirm'] = $this->arg->arguments['confirm'];
            return $options;
        }

        public function showHelp() {
            $cli = new Cli;
            $cli->printLine("Usage: object [\e[92maction\e[39m] [\e[96moptions\e[39m]");

            $cli->printLine();

            $cli->printLine("Actions: ");
            $cli->printLine();
            $cli->printLine("  \e[92mcreate\e[39m [\e[96moptions\e[39m]            Create a new object.");
            $cli->printLine("  \e[92mexists\e[39m [\e[96moptions\e[39m]            Check if an object exists.");
            $cli->printLine("  \e[92minfo\e[39m [\e[96moptions\e[39m]              Obtain information about an object.");
            $cli->printLine("  \e[92mlist\e[39m [\e[96moptions\e[39m]              List objects based on criteria.");
            $cli->printLine("  \e[92mproperties\e[39m [\e[96moptions\e[39m]        List properties within object.");
            $cli->printLine("  \e[92mpurge\e[39m [\e[96moptions\e[39m]             Purge an object and all it's properties.");
            $cli->printLine("  \e[92mget\e[39m [\e[96moptions\e[39m]               Retrieve the value of a property within an object.");
            $cli->printLine("  \e[92mproperty-exists\e[39m [\e[96moptions\e[39m]   Check if an object contains a property.");
            $cli->printLine("  \e[92mset\e[39m [\e[96moptions\e[39m]               Set the value of a property within an object.");
            $cli->printLine("  \e[92mdelete\e[39m [\e[96moptions\e[39m]            Delete a property within an object.");

            $cli->printLine();

            $cli->printLine("Options: ");
            $cli->printLine();
            $cli->printLine("  \e[96m--id\e[39m, \e[96m-i\e[39m                    The ID of the targeted migration:                     \e[90mx\e[39m");
            $cli->printLine("  \e[96m--type\e[39m, \e[96m-t\e[39m                  The type of the targeted object:                      \e[90mawesome-object-type\e[39m");
            $cli->printLine("  \e[96m--object\e[39m, \e[96m-o\e[39m                The name of the targeted object:                      \e[90mawesome-object-name\e[39m");
            $cli->printLine("  \e[96m--property\e[39m, \e[96m-p\e[39m              The targeted property within an object:               \e[90mawesome-object-property-name\e[39m");
            $cli->printLine("  \e[96m--delete\e[39m, \e[96m-d\e[39m                The value for a property within an object:            \e[90mawesome-object-property-value\e[39m");
            $cli->printLine("  \e[96m--limit\e[39m                     The maximum amount of results to be returned.");
            $cli->printLine("  \e[96m--offset\e[39m                    Amount of results to skip.");
            $cli->printLine("  \e[96m--confirm\e[39m                   Confirm an action.");
        }

        public function createColumn($value, $width) {
            return "  " . $value . join('', array_fill(0, $width - strlen(strval($value)), ' '));
        }
    }