<?php
    namespace Command;

    use \Library\Cli;
    use \Library\Objects;
    use \Registry\Cron as CronJobs;

    class Cron {
        public function execute($arg) {
            $this->arg = $arg;
            $cli = new Cli;

            if (isset($this->arg->arguments['help'])) {
                $cli->printLine();
                $this->showHelp();
                $cli->printLine();
                return;
            }

            switch($this->arg->details) {
                case "execute":
                    $options = $this->getOptions();
                    $options['verbose'] = isset($options['verbose']); 

                    if ($options['verbose']) $cli->printLine();
                    if ($options['verbose']) $cli->printLine("Obtaining list of cron jobs.");
                    $jobs = CronJobs::list();

                    if (isset($options['job'])) {
                        if (!empty($options['job'])) {
                            $options['job'] = \Helper\prepareFunctionNaming($options['job']);
                            if (in_array($options['job'], $jobs)) {
                                $this->run($options['job'], $options);
                            } else {
                                if (!$options['verbose']) $cli->printLine();
                                $cli->printLine("Cron job \"" . $options['job'] . "\" does not exist.");
                                $cli->printLine();
                                exit(2);
                            }
                        } else {
                            if (!$options['verbose']) $cli->printLine();
                            $cli->printLine("Name of cron job cannot be empty.");
                            $cli->printLine();
                            exit(1);
                        }
                    } else {
                        if ($options['verbose']) $cli->printLine("Looping through cron jobs.");
                        foreach($jobs as $name) {
                            $this->run($name, $options);
                        }

                        $time = time();
                        if ($options['verbose']) $cli->printLine();
                        if ($options['verbose']) $cli->printLine("Updating \"last-ran-system\" status to \"$time\".");
                        $object = new Objects;
                        if (!$object->exists('system', 'cron')) $object->create('system', 'cron');
                        $object->set('system', 'cron', 'last-ran-system', $time);
                    }
                    
                    if ($options['verbose']) $cli->printLine();
                    break;
                case "list": 
                    $cli->printLine();
                    $cli->printLine("This is a list of all the registered cron jobs.");
                    
                    $jobs = CronJobs::list();
                    foreach($jobs as $job) {
                        $cli->printLine("  - \e[92m$job\e[39m");
                    }
                    
                    $cli->printLine();
                    break;
                default:
                    if (isset($this->arg->arguments['help'])) {
                        $this->showHelp();
                    } else {
                        $cli->printLine((empty($this->arg->details) ? "No" : "Unknown") . " action defined! Try with --help");
                    }
            }
        }

        private function run($name, $options) {
            $cli = new Cli;
            if ($options['verbose']) $cli->printLine();
            if ($options['verbose']) $cli->printLine("Preparing cron job \"$name\".");
            $class = 'Cron\\' . $name;
            $job = new $class;

            if (!$job) {
                if ($options['verbose']) $cli->printLine("Failed to initiate job \"$name\".");
            } else if (!method_exists($job, 'execute')) {
                if ($options['verbose']) $cli->printLine("Invalid job \"$name\".");
            } else {
                if ($options['verbose']) $cli->printLine("Executing job \"$name\".");
                $job->execute($options);
                if ($options['verbose']) $cli->printLine("Finished job \"$name\".");
            }
        }

        public function getOptions() {
            $options = array();
            if (isset($this->arg->flags['j'])) $options['job'] = $this->arg->flags['j'];
            if (isset($this->arg->flags['v'])) $options['verbose'] = $this->arg->flags['v'];
            
            if (isset($this->arg->arguments['job'])) $options['job'] = $this->arg->arguments['job'];
            if (isset($this->arg->arguments['verbose'])) $options['verbose'] = $this->arg->arguments['verbose'];
            return $options;
        }

        public function showHelp() {
            $cli = new Cli;
            $cli->printLine("Usage: cron [\e[92maction\e[39m] [\e[96moptions\e[39m]");

            $cli->printLine();

            $cli->printLine("Actions: ");
            $cli->printLine();
            $cli->printLine("  \e[92mexecute\e[39m [\e[96moptions\e[39m]           Execute all or a single cron job.");
            $cli->printLine("  \e[92mlist\e[39m                        List all registered cron jobs.");

            $cli->printLine();

            $cli->printLine("Options: ");
            $cli->printLine();
            $cli->printLine("  \e[96m--job\e[39m, \e[96m-j\e[39m                   Target a specific job by it's name:                   \e[90mawesome-job-name\e[39m");
            $cli->printLine("  \e[96m--verbose\e[39m, \e[96m-v\e[39m               Enable logs of the cron jobs.");                      
        }

        public function createColumn($value, $width) {
            return "  " . $value . join('', array_fill(0, $width - strlen(strval($value)), ' '));
        }
    }