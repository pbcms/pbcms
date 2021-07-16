<?php
    namespace Library;

    class Objects {
        private $db = NULL;

        public function __construct() {
            $this->db = new Database();
        }

        public function create($type, $name) {
            if (empty($type) || empty($name)) {
                return false;
            } else {
                if ($this->exists($type, $name)) {
                    return false;
                } else {
                    $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "objects` (`type`, `name`) VALUES ('${type}', '${name}'");
                    return true;
                }
            }
        }

        public function exists($type, $name = '') {
            if (is_numeric($type) && $name == '') {
                $query = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "objects` WHERE `id`='${type}'";
            } else {
                $query = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "objects` WHERE `type`='${type}' AND `name`='${name}'";
            }

            $res = $this->db->query($query);
            if ($res->num_rows > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function info($type, $name = '') {
            if (is_numeric($type) && $name == '') {
                $query = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "objects` WHERE `id`='${type}'";
            } else {
                $query = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "objects` WHERE `type`='${type}' AND `name`='${name}'";
            }

            $res = $this->db->query($query);
            if ($res->num_rows > 0) {
                return (object) $res->fetch_assoc();
            } else {
                return false;
            }
        }

        public function list($limit = 10) {
            $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "objects` LIMIT ${limit}");
            return $res->fetch_all(MYSQLI_ASSOC);
        }

        public function properties($type, $name = '') {
            $obj = $this->info($type, $name);
            if ($obj == NULL) {
                return false;
            } else {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `object`='" . $obj->id . "'");
                return $res->fetch_all(MYSQLI_ASSOC);
            }
        }

        public function purge($type, $name = '') {
            $obj = $this->info($type, $name);
            if ($obj == NULL) {
                return false;
            } else {
                $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `object`='" . $obj->id . "'");
                $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "objects` WHERE `id`='" . $obj->id . "'");
                return true;
            }
        }

        public function get($type, $name = '', $property = '') {
            if ($property == '') {
                $obj = $this->info($type);
                $property = $name;
            } else {
                $obj = $this->info($type, $name);
            }

            if ($obj == NULL) {
                return false;
            } else {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `property`='${property}'");
                if ($res->num_rows > 0) {
                    return $res->fetch_assoc()['value'];
                } else {
                    return NULL;
                }
            }
        }

        public function set($type, $name = '', $property = '', $value = '') {
            if ($value == '' && $value !== 0) {
                $obj = $this->info($type);
                $value = $property;
                $property = $name;
            } else {
                $obj = $this->info($type, $name);
            }

            $value = strval($value);

            if ($obj == NULL) {
                return false;
            } else {
                if ($this->get($obj->id, $property) == NULL) {
                    $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "object-properties` (`object`, `property`, `value`) VALUES ('" . $obj->id . "', '${property}', '${value}')");
                } else {
                    $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "object-properties` SET `value`='${value}' WHERE `object`='" . $obj->id . "' AND `property`='${property}'");
                }

                return true;
            }
        }

        public function delete($type, $name = '', $property = '') {
            if ($property == '') {
                $obj = $this->info($type);
                $property = $name;
            } else {
                $obj = $this->info($type, $name);
            }

            if ($obj == NULL) {
                return false;
            } else {
                if ($this->get($obj->id, $property) != NULL) {
                    $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `object`='" . $obj->id . "' AND `property`='${property}'");
                }

                return true;
            }
        }
    }

    class ObjectPropertyWorker {
        private $obj;
        private $type;
        private $name;
        private $initialized = false;
        private $hardcoded = array();
        private $purgeLocked = false;

        protected function init($type, $name) {
            if ($this->initialized) return;
            $this->obj = new Objects;
            $this->type = $type;
            $this->name = $name;

            $this->obj->create($type, $name);
            $this->initialized = true;
        }

        protected function lockPurge() {
            if (!$this->initialized) return;
            $this->purgeLocked = true;
        }

        protected function unlockPurge() {
            if (!$this->initialized) return;
            $this->purgeLocked = false;
        }

        protected function lockProperty($property, $value) {
            if (!$this->initialized) return;
            $this->set($property, $value);
            $this->hardcoded[$property] = $value;
        }

        protected function unlockProperty($property) {
            if (!$this->initialized) return;
            $this->set($property, $this->hardcoded[$property]);
            unset($this->hardcoded[$property]);
        }

        public function get($property) {
            if (!$this->initialized) return;
            if (isset($hardcoded[$property])) return $hardcoded[$property];
            return $this->obj->get($this->type, $this->name, $property);
        }

        public function set($property, $value) {
            if (!$this->initialized) return;
            if (isset($hardcoded[$property])) return false;
            return $this->obj->set($this->type, $this->name, $property, $value);
        }

        public function delete($property) {
            if (!$this->initialized) return;
            if (isset($hardcoded[$property])) return false;
            return $this->obj->get($this->type, $this->name, $property);
        }

        public function properties() {
            if (!$this->initialized) return;
            $res = $this->obj->properties($this->type, $this->name);
            $final = array();

            foreach($res as $item) {
                $property = $item['property'];
                $final[$property] = $item['value']; 
            }

            foreach($this->hardcoded as $property => $value) {
                $final[$property] = $value;
            }

            return $final;
        }

        public function purge() {
            if (!$this->initialized) return;
            if ($this->purgeLocked) return false;
            return $this->obj->purge($this->type, $this->name);
        }
    }