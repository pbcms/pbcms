<?php
    namespace Command;

    use \Library\Cli;
    use \Library\Users;

    class User {
        public function execute($arg) {
            $this->arg = $arg;
            $cli = new Cli;
            $users = new Users;
            $options = $this->getOptions();

            $cli->printLine();

            if (isset($this->arg->arguments['help'])) {
                $this->showHelp();
                $cli->printLine();
                return;
            }

            switch($this->arg->details) {
                case "info":
                case "find":
                    if (isset($options['identifier'])) {
                        $res = $users->find($options['identifier'], $options['by_id_allowed']);
                        if (!$res) {
                            $cli->printLine("A user identifier by \"\e[96m" . $options['identifier'] . "\e[39m\" does not exist. (Filtering by ID is" . ($options['by_id_allowed'] ? ' ' : ' not ') . "allowed.)");
                        } else {
                            $cli->printLine("  ID:              \e[96m" . $res->id . "\e[39m");
                            $cli->printLine("  First name:      \e[96m" . $res->firstname . "\e[39m");
                            $cli->printLine("  Last name:       \e[96m" . $res->lastname . "\e[39m");
                            $cli->printLine("  E-mail:          \e[96m" . $res->email . "\e[39m");
                            $cli->printLine("  Username:        \e[96m" . $res->username . "\e[39m");
                            $cli->printLine("  Picture path:    \e[96m" . $res->picture->path . "\e[39m");
                            $cli->printLine("  Picture URL:     \e[96m" . $res->picture->url . "\e[39m");
                            $cli->printLine("  Type:            \e[96m" . $res->type . "\e[39m");
                            $cli->printLine("  Status:          \e[96m" . $res->status . "\e[39m");
                            $cli->printLine("  Created:         \e[96m" . $res->created . "\e[39m");
                            $cli->printLine("  Updated:         \e[96m" . $res->updated . "\e[39m");

                            if ($this->arg->details == 'info') {
                                $cli->printLine(" ");
                                $cli->printLine("  Meta details");
                                $cli->printLine(" ");

                                $meta = $users->metaList($res->id);

                                $longest = (object) array(
                                    "name" => 6,
                                    "value" => 7
                                );

                                foreach($meta as $item) {
                                    if (strlen($item['name']) + 2 > $longest->name) $longest->name = strlen($item['name']) + 2;
                                    if (strlen($item['value']) + 2 > $longest->value) $longest->value = strlen($item['value']) + 2;
                                }

                                if ($longest->name > 70) $longest->name = 70;
                                if ($longest->value > 70) $longest->value = 70;

                                $cli->printLine(str_replace(' ', '=', join("+", array(
                                    "",
                                    $this->createColumn("=", $longest->name),
                                    $this->createColumn("=", $longest->value),
                                    ""
                                ))));

                                $cli->printLine(join("|", array(
                                    "",
                                    $this->createColumn("Name", $longest->name),
                                    $this->createColumn("Value", $longest->value),
                                    ""
                                )));

                                $cli->printLine(str_replace(' ', '=', join("+", array(
                                    "",
                                    $this->createColumn("=", $longest->name),
                                    $this->createColumn("=", $longest->value),
                                    ""
                                ))));

                                foreach($meta as $item) {
                                    $cli->printLine(join("|", array(
                                        "",
                                        $this->createColumn((strlen($item['name']) > 68 ? substr($item['name'], 0, 65) . '...' : $item['name']), $longest->name),
                                        $this->createColumn((strlen($item['value']) > 68 ? substr($item['value'], 0, 65) . '...' : $item['value']), $longest->value),
                                        ""
                                    )));
                
                                    $cli->printLine(str_replace(' ', '-', join("+", array(
                                        "",
                                        $this->createColumn("-", $longest->name),
                                        $this->createColumn("-", $longest->value),
                                        ""
                                    ))));
                                }
                            }
                        }
                    } else {
                        $cli->printLine("Missing \e[96m--identifier\e[39m or \e[96m-i\e[39m option.");
                    }
                    
                    break;
                case "exists":
                    if (isset($options['identifier'])) {
                        $res = $users->find($options['identifier'], $options['by_id_allowed']);
                        if (!$res) {
                            $cli->printLine("A user identifier by \"\e[96m" . $options['identifier'] . "\e[39m\" does not exist. (Filtering by ID is" . ($options['by_id_allowed'] ? ' ' : ' not ') . "allowed.)");
                        } else {
                            $cli->printLine("A user identifier by \"\e[96m" . $options['identifier'] . "\e[39m\" exists. (Filtering by ID is" . ($options['by_id_allowed'] ? ' ' : ' not ') . "allowed.)");
                        }
                    } else {
                        $cli->printLine("Missing \e[96m--identifier\e[39m or \e[96m-i\e[39m option.");
                    }

                    break;
                case "list":
                    $list = $users->list($options);

                    $longest = (object) array(
                        "id" => 4,
                        "firstname" => 11,
                        "lastname" => 10,
                        "email" => 8,
                        "username" => 10,
                        "type" => 6,
                        "status" => 8,
                        "created" => 9,
                        "updated" => 9
                    );

                    foreach($list as $item) {
                        $item = (object) $item;
                        if (strlen(strval($item->id)) + 2 > $longest->id) $longest->id = strlen(strval($item->id)) + 2;
                        if (strlen(strval($item->firstname)) + 2 > $longest->firstname) $longest->firstname = strlen(strval($item->firstname)) + 2;
                        if (strlen(strval($item->lastname)) + 2 > $longest->lastname) $longest->lastname = strlen(strval($item->lastname)) + 2;
                        if (strlen(strval($item->email)) + 2 > $longest->email) $longest->email = strlen(strval($item->email)) + 2;
                        if (strlen(strval($item->username)) + 2 > $longest->username) $longest->username = strlen(strval($item->username)) + 2;
                        if (strlen(strval($item->type)) + 2 > $longest->type) $longest->type = strlen(strval($item->type)) + 2;
                        if (strlen(strval($item->status)) + 2 > $longest->status) $longest->status = strlen(strval($item->status)) + 2;
                        if (strlen(strval($item->created)) + 2 > $longest->created) $longest->created = strlen(strval($item->created)) + 2;
                        if (strlen(strval($item->updated)) + 2 > $longest->updated) $longest->updated = strlen(strval($item->updated)) + 2;
                    }

                    $cli->printLine(str_replace(' ', '=', join("+", array(
                        "",
                        $this->createColumn("=", $longest->id),
                        $this->createColumn("=", $longest->firstname),
                        $this->createColumn("=", $longest->lastname),
                        $this->createColumn("=", $longest->email),
                        $this->createColumn("=", $longest->username),
                        $this->createColumn("=", $longest->type),
                        $this->createColumn("=", $longest->status),
                        $this->createColumn("=", $longest->created),
                        $this->createColumn("=", $longest->updated),
                        ""
                    ))));

                    $cli->printLine(join("|", array(
                        "",
                        $this->createColumn("ID", $longest->id),
                        $this->createColumn("Firstname", $longest->firstname),
                        $this->createColumn("Lastname", $longest->lastname),
                        $this->createColumn("E-mail", $longest->email),
                        $this->createColumn("Username", $longest->username),
                        $this->createColumn("Type", $longest->type),
                        $this->createColumn("Status", $longest->status),
                        $this->createColumn("Created", $longest->created),
                        $this->createColumn("Updated", $longest->updated),
                        ""
                    )));

                    $cli->printLine(str_replace(' ', '=', join("+", array(
                        "",
                        $this->createColumn("=", $longest->id),
                        $this->createColumn("=", $longest->firstname),
                        $this->createColumn("=", $longest->lastname),
                        $this->createColumn("=", $longest->email),
                        $this->createColumn("=", $longest->username),
                        $this->createColumn("=", $longest->type),
                        $this->createColumn("=", $longest->status),
                        $this->createColumn("=", $longest->created),
                        $this->createColumn("=", $longest->updated),
                        ""
                    ))));

                    foreach($list as $item) {
                        $item = (object) $item;
                        $cli->printLine(join("|", array(
                            "",
                            $this->createColumn($item->id, $longest->id),
                            $this->createColumn($item->firstname, $longest->firstname),
                            $this->createColumn($item->lastname, $longest->lastname),
                            $this->createColumn($item->email, $longest->email),
                            $this->createColumn($item->username, $longest->username),
                            $this->createColumn($item->type, $longest->type),
                            $this->createColumn($item->status, $longest->status),
                            $this->createColumn($item->created, $longest->created),
                            $this->createColumn($item->updated, $longest->updated),
                            ""
                        )));
    
                        $cli->printLine(str_replace(' ', '-', join("+", array(
                            "",
                            $this->createColumn("-", $longest->id),
                            $this->createColumn("-", $longest->firstname),
                            $this->createColumn("-", $longest->lastname),
                            $this->createColumn("-", $longest->email),
                            $this->createColumn("-", $longest->username),
                            $this->createColumn("-", $longest->type),
                            $this->createColumn("-", $longest->status),
                            $this->createColumn("-", $longest->created),
                            $this->createColumn("-", $longest->updated),
                            ""
                        ))));
                    }

                    break;
                case "create":

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
            $options['by_id_allowed'] = true;

            if (isset($this->arg->flags['i'])) $options['identifier'] = $this->arg->flags['i'];
            if (isset($this->arg->flags['v'])) $options['value'] = $this->arg->flags['v'];
            if (isset($this->arg->flags['l'])) $options['limit'] = $this->arg->flags['l'];
            if (isset($this->arg->flags['o'])) $options['offset'] = $this->arg->flags['o'];
                    
            if (isset($this->arg->arguments['identifier'])) $options['identifier'] = $this->arg->arguments['identifier'];
            if (isset($this->arg->arguments['no-id'])) $options['by_id_allowed'] = false;

            if (isset($this->arg->arguments['id'])) $options['id'] = $this->arg->arguments['id'];
            if (isset($this->arg->arguments['firstname'])) $options['firstname'] = $this->arg->arguments['firstname'];
            if (isset($this->arg->arguments['lastname'])) $options['lastname'] = $this->arg->arguments['lastname'];
            if (isset($this->arg->arguments['email'])) $options['email'] = $this->arg->arguments['email'];
            if (isset($this->arg->arguments['username'])) $options['username'] = $this->arg->arguments['username'];
            if (isset($this->arg->arguments['type'])) $options['type'] = $this->arg->arguments['type'];
            if (isset($this->arg->arguments['status'])) $options['status'] = $this->arg->arguments['status'];
            if (isset($this->arg->arguments['created'])) $options['created'] = $this->arg->arguments['created'];
            if (isset($this->arg->arguments['updated'])) $options['updated'] = $this->arg->arguments['updated'];

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