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
                    if (!$this->validateNaming($type) || !$this->validateNaming($name)) return false;
                    $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "objects` (`type`, `name`) VALUES ('${type}', '${name}')");
                    return true;
                }
            }
        }

        public function validateNaming($str) {
            if (preg_match("/[^A-Za-z0-9-_.]/", $str)) {
                return false;
            } else {
                if (strlen(strval(intval($str))) == strlen($str)) {
                    return false; //Contains only numbers. Types and Names should not interfere with object IDs.
                } else {
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

        public function list($arg1 = null, $arg2 = null, $arg3 = null) {
            if (!$arg1) {
                $type = null; $limit = 10; $offset = 0; $filters = null;
            } else if (is_numeric($arg1)) {
                $type = null; $limit = $arg1; $offset = (is_numeric($arg2) ? $arg2 : 0); $filters = null;
            } else if (is_string($arg1)) {
                $type = $arg1; $limit = (is_numeric($arg2) ? $arg2 : 10); $offset = (is_numeric($arg3) ? $arg3 : 0); $filters = null;
            } else if (is_object($arg1) || is_array($arg1)) {
                $options = (object) $arg1; $type = (!isset($options->type) ? null : $options->type); $limit = (!isset($options->limit) ? 10 : $options->limit); $offset = (!isset($options->offset) ? 0 : $options->offset); $filters = (!isset($options->filters) ? null : (array) $options->filters);
            }

            $sql = "SELECT " . (isset($options->count) && $options->count ? 'COUNT(`id`) as count' : '*') . " FROM `" . DATABASE_TABLE_PREFIX . "objects` WHERE ";
            $sql .= ($type ? "`type`='${type}'" : "1");

            if (isset($options->filters) && count($options->filters) > 0) {
                foreach($options->filters as $filter) {
                    $filter = (object) $filter;
                    switch($filter->target) {
                        case 'property':
                            if (!isset($filter->compare)) $filter->compare = "=";
                            if (!in_array(strtoupper($filter->compare), ['like', '=', '<', '<=', '>', '>='])) $filter->compare = "=";
                            if (isset($filter->search) && $filter->search) $filter->compare = 'LIKE';
                            $filter->compare = strtoupper($filter->compare);

                            $filter->type = (isset($filter->type) && (strtolower($filter->type) == 'excluded' || strtolower($filter->type) == 'exclude') ? "NOT IN" : "IN");
                            if (isset($filter->search) && $filter->search) $filter->value = "%$filter->value%";

                            $sql .= " AND `" . DATABASE_TABLE_PREFIX . "objects`.`id` " . $filter->type . " (";
                            $sql .= "SELECT `".DATABASE_TABLE_PREFIX . "object-properties`.`object` FROM `" . DATABASE_TABLE_PREFIX . "object-properties`";
                            $sql .= " WHERE `".DATABASE_TABLE_PREFIX . "object-properties`.`property`='".$filter->property."'";
                            $sql .= " AND `" . DATABASE_TABLE_PREFIX . "object-properties`.`value` " . $filter->compare . " '".$filter->value."')";
                            break;
                        case 'relation':
                            $filter->type = (isset($filter->type) && (strtolower($filter->type) == 'excluded' || strtolower($filter->type) == 'exclude') ? "NOT IN" : "IN");
                            $sql .= " AND `" . DATABASE_TABLE_PREFIX . "objects`.`id` " . $filter->type . " (";
                            $sql .= "SELECT `".DATABASE_TABLE_PREFIX . "relations`.`" . $filter->item . "` FROM `" . DATABASE_TABLE_PREFIX . "relations`";
                            $sql .= " WHERE `".DATABASE_TABLE_PREFIX . "relations`.`type`='".$filter->relation."')";
                            break;
                    }
                }
            }

            if (isset($options->limit)) $sql .= " LIMIT " . ($options->limit < 1 ? '18446744073709551610' : $options->limit);
            if (isset($options->offset)) $sql .= " OFFSET " . $options->offset;
            if (isset($options->order)) $sql .= " ORDER BY `id` " . (strtolower($options->order) == 'desc' ? "DESC" : "ASC");
        
            $res = $this->db->query($sql);
            if (isset($options->count) && $options->count) {
                $res = (object) $res->fetch_assoc();
                return $res->count;
            } else if ($res->num_rows > 0) {
                return $res->fetch_all(MYSQLI_ASSOC);
            } else {
                return array();
            }
        }

        public function count($input = array()) {
            $input = (object) $input;
            $input->count = true;
            $input->limit = 0;
            $input->offset = 0;
            return $this->list($input);
        }

        public function properties($type, $name = '', $parse = null) {
            $obj = $this->info($type, (is_bool($name) && $parse == null ? '' : $name));
            $parse = (is_bool($name) && $parse == null ? $name : ($parse == null ? false : true));
            if ($obj == NULL) {
                return false;
            } else {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `object`='" . $obj->id . "'");
                $properties = $res->fetch_all(MYSQLI_ASSOC);
                if ($parse) {
                    $parsed = array();
                    foreach($properties as $property) $parsed[$property['property']] = $property['value'];
                    return $parsed;
                } else {
                    return $properties;
                }
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
                $id = $obj->id;
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `object`='${id}' AND `property`='${property}'");
                if ($res->num_rows > 0) {
                    return $res->fetch_assoc()['value'];
                } else {
                    return NULL;
                }
            }
        }

        public function propertyExists($type, $name = '', $property = '') {
            if ($property == '') {
                $obj = $this->info($type);
                $property = $name;
            } else {
                $obj = $this->info($type, $name);
            }


            if ($obj == NULL) {
                return false;
            } else {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "object-properties` WHERE `object`='$obj->id' AND `property`='${property}'");
                return $res->num_rows > 0;
            }
        }

        public function set($type, $name = '', $property = '', $value = false) {
            if (is_bool($value) && !$value) {
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
                if (!$this->propertyExists($obj->id, $property)) {
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

        protected function existsInDatabase($property) {
            if (!$this->initialized) return;
            return $this->obj->propertyExists($this->type, $this->name, $property);
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