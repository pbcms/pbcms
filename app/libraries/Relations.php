<?php
    namespace Library;

    use Helper\Validate as Validator;

    class Relations {
        private $db = NULL;
        private $filterAllowedProperties = array("id", "type", "originType", "targetType", "origin", "target");

        public function __construct() {
            $this->db = new Database;
        }

        public function create($type, $origin, $target) {
            if ($this->find($type, $origin, $target) == NULL) {
                $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "relations` (`type`, `origin`, `target`) VALUES ('${type}', '${origin}', '${target}')");
                return true;
            } else {
                return false;
            }
        }

        public function delete($type, $origin = null, $target = null) {
            $relation = $this->find($type, $origin, $target);
            if ($relation) {
                $relId = $relation->id;
                $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "relations` WHERE `id`='${relId}'");
                return true;
            } else {
                return false;
            }
        }

        public function list($input = array()) {
            $input = (object) $input;
            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "relations`";

            if (is_string($input)) {
                $res = $this->db->query($sql . " WHERE `type`='${input}'");
            } else {
                if (count(array_keys(get_object_vars($input))) > 0) {
                    $allowedFilters = array("limit", "offset", "order");
    
                    $filters = (object) Validator::removeUnlisted($allowedFilters, $input);
                    $properties = (object) Validator::removeUnlisted($this->filterAllowedProperties, $input);
    
                    if (count(array_keys(get_object_vars($properties))) > 0) {
                        $sql .= " WHERE";
                        foreach($properties as $key => $value) {
                            if (array_keys(get_object_vars($properties))[0] !== $key) $sql .= " AND";
                            switch($key) {
                                case 'originType':
                                    $sql .= " `type` LIKE '${value}:%'";
                                    break;
                                case 'targetType':
                                    $sql .= " `type` LIKE '%:${value}'";
                                    break;
                                default:
                                    $sql .= " `${key}`='${value}'";
                            }
                        }
                    }
    
                    if (isset($filters->limit)) $sql .= " LIMIT " . $filters->limit;
                    if (isset($filters->offset)) $sql .= " OFFSET " . $filters->offset;
                    if (isset($filters->order)) $sql .= " ORDER BY `id` " . (strtolower($filters->order) == 'desc' ? "DESC" : "ASC");
                }
    
                $res = $this->db->query($sql);
            }

            if ($res->num_rows > 0) {
                return (array) $res->fetch_all(MYSQLI_ASSOC);
            } else {
                return array();
            }
        }

        public function find($type, $origin = null, $target = null) {
            if (is_numeric($type) && !$origin && !$target) {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "relations` WHERE `id`='${type}'");
            } else {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "relations` WHERE `type`='${type}' AND `origin`='${origin}' AND `target`='${target}'");
            }

            if ($res->num_rows > 0) {
                $res = (object) $res->fetch_assoc();
                $res->id = intval($res->id);
                $res->origin = intval($res->origin);
                $res->target = intval($res->target);
                return $res;
            } else {
                return NULL;
            }
        }
    }