<?php
    namespace Library;

    require_once APP_DIR . '/cli/Clear.php';
    require_once APP_DIR . '/cli/Help.php';
    require_once APP_DIR . '/cli/Info.php';
    require_once APP_DIR . '/cli/Ping.php';
    require_once APP_DIR . '/cli/Database.php';
    require_once APP_DIR . '/cli/User.php';
    require_once APP_DIR . '/cli/Permission.php';
    require_once APP_DIR . '/cli/Language.php';
    require_once APP_DIR . '/cli/Module.php';
    require_once APP_DIR . '/cli/Policy.php';
    require_once APP_DIR . '/cli/Session.php';
    require_once APP_DIR . '/cli/VirtualPath.php';

    class Cli {
        private static $shell = false;
        private static $commands = array(
            "clear" => "Clear the console.",
            "help" => "Use help for a list of commands.",
            "info" => "Information about the PBCMS installation and command line interface.",
            "shell" => false,
            "ping" => false,
            "exit" => false,
            "database" => "A utility to migrate and rollback the database.",
            "user" => "Quickly administer users from the command-line, or create temporary credentials.",
            "permission" => "Grant or revoke permissions to groups and users.",
            "language" => "Retrieve a language string.",
            "module" => "Administer modules from the command line.",
            "policy" => "Change and update policies.",
            "session" => "Revoke sessions.",
            "virtual-path" => "Fix broken virtual paths."
        );

        public function process($input) {
            $request = self::parseArguments($input);
            if ($request->command == "exit" || $request->command == "quit") die("Bye!");
            if ($request->command == "shell") {
                self::$shell = true;
                $this->keepAlive();
            } else {
                $cmd = $this->request($request->command);
                if (!$cmd) {
                    $this->printLine("Error! The command \"" . $request->command . "\" does not exist.");
                    $this->keepAlive();
                } else {
                    $cmd->execute($request);
                    $this->keepAlive();
                }
            }
        }

        private function keepAlive() {
            if (self::$shell) {
                if (!defined("SITE_TITLE")) define("SITE_TITLE", "Rescue shell");
                $prompt = $this->prompt(SITE_TITLE . "@PBCMS ~> ");
                $this->process($prompt);
            }
        }

        public function register($command, $description) {
            if (isset(self::$commands[$command])) {
                return false;
            } else {
                self::$commands[$command] = $description;
                return true;
            }
        }

        public function exists($command) {
            return isset(self::$commands[$command]);
        }

        public function list() {
            return self::$commands;
        }

        public static function parseArguments($input) {
            $input = (is_string($input) ? $input : join(' ', $input));          //Correctly format input.
            $parsed = explode(' ', $input);                                     //Exploded input.
            $currentArgument = NULL;
            $currentFlag = NULL;
            $result = (object) array(
                "command" => NULL,
                "details" => NULL,
                "arguments" => array(),
                "flags" => array()
            );

            for($i = 0; $i < count($parsed); $i++) {
                $segment = trim($parsed[$i]);
                if ($i == 0) {
                    $result->command = $segment;
                } else {
                    if (substr($segment, 0, 2) == '--') {
                        $currentFlag = NULL;
                        $currentArgument = substr($segment, 2);
                        $result->arguments[$currentArgument] = "";
                    } else if (substr($segment, 0, 1) == '-') {
                        $currentArgument = NULL;
                        $currentFlag = substr($segment, 1);
                        $result->flags[$currentFlag] = "";
                    } else {
                        if ($currentArgument) {
                            $result->arguments[$currentArgument] .= ($result->arguments[$currentArgument] == "" ? "" : " ") . $segment;
                        } else if ($currentFlag) {
                            $result->flags[$currentFlag] .= ($result->flags[$currentFlag] == "" ? "" : " ") . $segment;
                        } else {
                            if (!$result->details) $result->details = "";
                            $result->details .= ($result->details == "" ? "" : " ") . $segment;
                        }
                    }
                }
            }


            return $result;
        }

        public function prepareFunctionNaming($str) {
            $str = str_replace('-', ' ', $str);
            $str = ucwords($str);
            $str = str_replace(' ', '', $str);
            return $str;
        }

        public function prompt($prefix = "$ ") {
            $this->print($prefix);
            return trim(fgets(STDIN));
        }

        private function request($command) {
            if (isset(self::$commands[$command])) {
                $class = 'Command\\' . $this->prepareFunctionNaming($command);
                $cmd = new $class;
                return $cmd;
            } else {
                return NULL;
            }
        }

        public function print($text = "") {
            fwrite(STDOUT, $text);
        }

        public function printLine($text = "") {
            $this->print($text . PHP_EOL);
        }
    }