<?php
    namespace Library;

    class Database {
        private $hostname = DATABASE_HOSTNAME;
        private $username = DATABASE_USERNAME;
        private $password = DATABASE_PASSWORD;
        private $database = DATABASE_DATABASE;
        protected static $conn = NULL;

        public function __construct() {
            $this->insert_id = NULL;
            if (self::$conn == NULL) self::$conn = new \mysqli($this->hostname, $this->username, $this->password, $this->database);

            if (self::$conn->connect_error) {
                die("Error 500: Connection to the database failed!");
            }
        }

        public function table($name) {
            return DATABASE_TABLE_PREFIX . $name;
        }

        public function query($sql) {
            $res = self::$conn->query($sql);
            $this->insert_id = self::$conn->insert_id;
            return $res;
        }

        public function escape($input) {
            return self::$conn->real_escape_string($input);
        }

        public function escapeObject($data) {
            $data = (object) $data;
            foreach($data as $key => $value) $data->{$key} = self::$conn->real_escape_string($value);
            return $data;
        }
    }

    class DatabaseMigrator {
        private $db;
        private $shout = true;
        private $logs = array();
        private $logger;

        public function __construct($options = array()) {
            $this->db = new Database;
            $this->createMigrationsTable();

            if (is_array($options)) {
                foreach($options as $option => $value) {
                    switch($option) {
                        case 'shout':
                            if (is_bool($value)) $this->shout = $value;
                            break;
                    }
                }
            }

            $this->logger = function($message) {
                array_push($this->logs, $message);
                if ($this->shout) \Core::PrintLine($message);
            };
        }

        public function migrate() {
            $this->createMigrationsTable();
            $this->log("Started migrating.");
            
            $queue = $this->availableMigrations();
            $queueCount = count($queue);
            
            if ($queueCount > 0) {
                $this->log("Queued ${queueCount} migration" . ($queueCount == 1 ? '' : 's') . ".");
                $this->log("Start processing the queue.");

                $updateMigrationsTableQueries = array();
                for($i = 0; $i < $queueCount; $i++) {
                    $start = microtime(true);
                    $migration = $queue[$i];
                    $this->log("#" . ($i+1) . ": Starting migration for: $migration->name. (VERSION: $migration->version; TASK: $migration->task)");

                    $class = "DatabaseMigrator\\" . $migration->classname;
                    require_once(dirname(__DIR__) . '/sources/migrations/' . $migration->file);
                    $migrator = new $class();
                    $migrator->up(new Database, $this->logger);

                    array_push($updateMigrationsTableQueries, "('$migration->migration', '$migration->version', '$migration->task', '$migration->name')");
                    $this->log("#" . ($i+1) . ": Finished migration for: $migration->name. (VERSION: $migration->version; TASK: $migration->task; TOOK: " . number_format(microtime(true) - $start, 4, '.', '') . "s)");
                }

                $query = "INSERT INTO `" . DATABASE_TABLE_PREFIX . "migrations` (`migration`, `version`, `task`, `name`) VALUES " . join(', ', $updateMigrationsTableQueries) . ";";
                $res = $this->db->query($query);
                if (!$res) {
                    $this->log("Unable to insert newly executed migrations into migrations database. It is critical to insert the following manually before you continue:");
                    print_r(json_encode($queue));
                    echo PHP_EOL . PHP_EOL;
                    $this->log("The following query was used: " . $query);
                } else {
                    $this->log("Updated migrations table.");
                }

                $this->log("Finished all migrations.");
            } else {
                $this->log("No migrations queued, finished migrating.");
            }

            return true;
        }

        public function rollback($filters = NULL) {
            $this->createMigrationsTable();
            $this->log("Started rollbacking migrations.");

            $queue = array();
            $files = glob(dirname(__DIR__) . '/sources/migrations/*.*.*-*-*.php');
            $processFilePaths = function ($fp) { 
                $file = basename($fp);
                $migration = substr($file, 0, -4);
                $segments = explode('-', $migration);
                $uppercaseFirst = function ($w) { return ucfirst($w); };
                return (object) array(
                    "file" => $file,
                    "migration" => $migration,
                    "version" => $segments[0],
                    "task" => $segments[1],
                    "name" => $segments[2],
                    "classname" => str_replace('.', '_', join('', array_map($uppercaseFirst, explode('_', $segments[2]))) . '__' . $segments[1] . '__' . $segments[0])
                );
            };

            $files = array_map($processFilePaths, $files);
            $availableRollbacksCount = count($files);
            $queueCount = count($queue);
            $this->log("Found ${availableRollbacksCount} migration script" . ($availableRollbacksCount == 1 ? '' : 's') . ", ${queueCount} rollback" . ($queueCount == 1 ? '' : 's') . " queued.");

            $this->log("Gathering a list of eligable rollbacks per (defined) filter.");
            $getEarliestMigrationQuery = "SELECT `id` FROM `" . DATABASE_TABLE_PREFIX . "migrations`";
            if ($filters == NULL) $filters = array("version"=>$this->getLastVersion());
            if (is_array($filters) || is_object($filters)) {
                $filters = (object) $filters;
                $queryFilters = array();
                foreach($filters as $key => $value) {
                    switch($key) {
                        case 'id':
                            array_push($queryFilters, "`id`='${value}'");
                            break;
                        case 'migration':
                            array_push($queryFilters, "`migration`='${value}'");
                            break;
                        case 'version':
                            array_push($queryFilters, "`version`='${value}'");
                            break;
                        case 'task':
                            array_push($queryFilters, "`task`='${value}'");
                            break;
                        case 'name':
                            array_push($queryFilters, "`name`='${value}'");
                            break;
                    }
                }

                if (count($queryFilters) > 0) $getEarliestMigrationQuery .= " WHERE " . join(" AND ", $queryFilters);
                $getEarliestMigrationQuery .= " ORDER BY `migration` ASC LIMIT 1";
            } else {
                return NULL;
            }

            $earliestMigration = $this->db->query($getEarliestMigrationQuery);
            if ($earliestMigration->num_rows > 0) {
                $earliestMigration = $earliestMigration->fetch_assoc()['id'];
            } else {
                $this->log("Failed to obtain earliest migration. Quitting.");
                return NULL;
            }
            
            $retrieveRollbacksQuery = "SELECT `migration` FROM `" . DATABASE_TABLE_PREFIX . "migrations` WHERE `id`>='$earliestMigration' ORDER BY `migration` DESC";
            $retrievedRollbacksRaw = $this->db->query($retrieveRollbacksQuery);
            $retrievedRollbacks = $retrievedRollbacksRaw->fetch_all(MYSQLI_ASSOC);
            $processRetrievedRollbacks = function($row) { return join('-', array_slice(explode('-', $row['migration']), 0, 2)); };
            $retrievedRollbacks = array_map($processRetrievedRollbacks, $retrievedRollbacks);

            $rollbackCount = count($retrievedRollbacks);
            $this->log("Gathered ${rollbackCount} eligable migration" . ($rollbackCount == 1 ? '' : 's') . " for rollback.");

            $quit = false;
            foreach($retrievedRollbacks as $requiredRollback) {
                $found = false;
                foreach($files as $item) {
                    if ($item->version . '-' . $item->task == $requiredRollback) {
                        $found = true;
                        array_push($queue, $item);
                    }
                }

                if (!$found) {
                    $quit = true;
                    $this->log("Could not find a migration file for rollback: " . $requiredRollback);
                }
            }

            if ($quit) {
                $this->log("Not proceeding rollback due to error(s).");
                return NULL;
            }

            $queueCount = count($queue);
            $eliminatedMigrations = $availableRollbacksCount - $queueCount;
            $this->log("Skipping ${eliminatedMigrations} of the ${availableRollbacksCount} available migration" . ($availableRollbacksCount == 1 ? '' : 's') . " eligable for rollback, ${queueCount} rollback" . ($queueCount == 1 ? '' : 's') . " queued.");
            
            if ($queueCount > 0) {
                $this->log("Start processing the queue.");

                for($i = 0; $i < $queueCount; $i++) {
                    $start = microtime(true);
                    $migration = $queue[$i];
                    $this->log("#" . ($i+1) . ": Starting rollback for: $migration->name. (VERSION: $migration->version; TASK: $migration->task)");

                    $class = "DatabaseMigrator\\" . $migration->classname;
                    require_once(dirname(__DIR__) . '/sources/migrations/' . $migration->file);
                    $migrator = new $class();
                    $migrator->down(new Database, $this->logger);

                    $this->log("#" . ($i+1) . ": Finished rollback for: $migration->name. (VERSION: $migration->version; TASK: $migration->task; TOOK: " . number_format(microtime(true) - $start, 4, '.', '') . "s)");
                }

                $query = "DELETE FROM `" . DATABASE_TABLE_PREFIX . "migrations` WHERE `id`>=${earliestMigration}";
                $res = $this->db->query($query);
                if (!$res) {
                    $this->log("Unable to delete the migration entries in the migrations table. Delete each row assigned an id equal or greater than ${earliestMigration} before you continue!");
                } else {
                    $this->log("Updated migrations table.");
                }

                $this->log("Finished rollbacks for each migration.");
            } else {
                $this->log("No rollbacks queued, finished rollbacking.");
            }

            return true;
        }

        public function getMigrationInfo($identifier) {
            $this->createMigrationsTable();
            $query = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "migrations` WHERE `id`='${identifier}'";
            if (strpos($identifier, '-') !== false) $query .= " OR `migration` LIKE '$identifier-%'";
            
            $res = $this->db->query($query);
            return $res->fetch_object();
        }

        public function listMigrations($filters = NULL) {
            $this->createMigrationsTable();
            $translateArrayToObject = function ($row) { return (object) $row; };
            $query = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "migrations`";

            if (is_array($filters) || is_object($filters)) {
                $filters = (object) $filters;
                $queryFilters = array();
                foreach($filters as $key => $value) {
                    switch($key) {
                        case 'id':
                            array_push($queryFilters, "`id`='${value}'");
                            break;
                        case 'migration':
                            array_push($queryFilters, "`migration`='${value}'");
                            break;
                        case 'version':
                            array_push($queryFilters, "`version`='${value}'");
                            break;
                        case 'task':
                            array_push($queryFilters, "`task`='${value}'");
                            break;
                        case 'name':
                            array_push($queryFilters, "`name`='${value}'");
                            break;
                    }
                }

                if (count($queryFilters) > 0) $query .= " WHERE " . join(" AND ", $queryFilters);
                if (isset($filters->limit)) $query .= " LIMIT $filters->limit";
                if (isset($filters->offset)) $query .= " OFFSET $filters->offset";
                if (isset($filters->order_by)) $filters->orderby = $filters->order_by;
                if (isset($filters->orderby)) {
                    $query .= " ORDER BY `$filters->orderby`";
                } else {
                    $query .= " ORDER BY `id`";
                }

                if (isset($filters->order)) $query .= " $filters->order";
            }

            $res = $this->db->query($query);
            $data = $res->fetch_all(MYSQLI_ASSOC);

            return array_map($translateArrayToObject, $data);
        }

        public function availableMigrations() {
            $this->createMigrationsTable();
            $this->log("Searching for available migrations.");

            $available = array();
            $migrations = glob(dirname(__DIR__) . '/sources/migrations/*.*.*-*-*.php');
            natsort($migrations);

            $processFilePaths = function ($fp) { 
                $file = basename($fp);
                $migration = substr($file, 0, -4);
                $segments = explode('-', $migration);
                $uppercaseFirst = function ($w) { return ucfirst($w); };
                return (object) array(
                    "file" => $file,
                    "migration" => $migration,
                    "version" => $segments[0],
                    "task" => $segments[1],
                    "name" => $segments[2],
                    "classname" => str_replace('.', '_', join('', array_map($uppercaseFirst, explode('_', $segments[2]))) . '__' . $segments[1] . '__' . $segments[0])
                );
            };

            $migrations = array_map($processFilePaths, $migrations);
            $migrationCount = count($migrations);
            $availableCount = count($available);
            $this->log("Found ${migrationCount} migration script" . ($migrationCount == 1 ? '' : 's') . ".");

            $retrieveMigratedQuery = "SELECT `migration` FROM `" . DATABASE_TABLE_PREFIX . "migrations`";
            $retrievedMigratedRaw = $this->db->query($retrieveMigratedQuery);
            $retrievedMigrated = $retrievedMigratedRaw->fetch_all(MYSQLI_ASSOC);
            $processRetrievedMigrated = function($row) { return join('-', array_slice(explode('-', $row['migration']), 0, 2)); };
            $retrievedMigrated = array_map($processRetrievedMigrated, $retrievedMigrated);

            foreach($migrations as $migration) {
                if (!in_array($migration->version . '-' . $migration->task, $retrievedMigrated)) {
                    array_push($available, $migration);
                }
            }

            $availableCount = count($available);
            $eliminatedMigrations = $migrationCount - $availableCount;
            $this->log("Eliminated ${eliminatedMigrations} of the ${migrationCount} migration" . ($migrationCount == 1 ? '' : 's') . ", ${availableCount} migration" . ($availableCount == 1 ? '' : 's') . " available to be executed.");
            return $available;
        }

        public function getLastVersion() {
            $this->createMigrationsTable();
            $res = $this->db->query("SELECT `version` FROM `" . DATABASE_TABLE_PREFIX . "migrations` ORDER BY `version` DESC LIMIT 1");
            if ($res->num_rows > 0) {
                return $res->fetch_assoc()['version'];
            } else {
                return NULL;
            }
        }

        private function createMigrationsTable() {
            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DATABASE_TABLE_PREFIX . "migrations` (`id` INT AUTO_INCREMENT PRIMARY KEY, `migration` VARCHAR(500) UNIQUE, `version` VARCHAR(16), `task` INT, `name` VARCHAR(450), `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=INNODB;");
        }
    
        private function log($message, $appendTimestamp = true) {
            if ($appendTimestamp) $message = '[' . date("Y-m-d H:i:s") . '] ~ ' . $message;
            ($this->logger)($message);
        }

        public function retrieveLogs() {
            return $this->logs;
        }
    }