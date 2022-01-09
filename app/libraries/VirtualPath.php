<?php
    namespace Library;

    use Helper\Validate as Validator;

    class VirtualPath {
        private $db = NULL;
        private $filterAllowedProperties = array("id", "path", "target", "lang");

        public function __construct() {
            $this->db = new Database;
        }

        function create($path, $target, $lang) {
            if ($this->find($path, $target, $lang) == NULL) {
                $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "virtual-paths` (`path`, `target`, `lang`) VALUES ('${path}', '${target}', '${lang}')");
                return true;
            } else {
                return false;
            }
        }

        function find($path, $target = null, $lang = null) {
            if (is_numeric($path) && !$target && !$lang) {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "virtual-paths` WHERE `id`='${path}'");
            } else {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "virtual-paths` WHERE `path`='${path}' AND `target`='${target}' AND `lang`='${lang}'");
            }

            if ($res->num_rows > 0) {
                $res = (object) $res->fetch_assoc();
                $res->id = intval($res->id);
                return $res;
            } else {
                return NULL;
            }
        }

        function list($input = array()) {
            $input = (object) $input;
            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "virtual-paths`";

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

            if ($res->num_rows > 0) {
                return (array) $res->fetch_all(MYSQLI_ASSOC);
            } else {
                return array();
            }
        }

        function delete($path, $target = null, $lang = null) {
            $vPath = $this->find($path, $target, $lang);
            if ($vPath) {
                $vPathId = $vPath->id;
                $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "virtual-paths` WHERE `id`='${vPathId}'");
                return true;
            } else {
                return false;
            }
        }
    }