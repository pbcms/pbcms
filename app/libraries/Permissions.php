<?php
    namespace Library;

    use Helper\Validate;

    class Permissions {
        private $db = NULL;
        private $filterAllowedProperties = array("id", "target", "targetType", "targetValue", "permission", "granted", "created", "updated");

        public function __construct() {
            $this->db = new Database;
        }

        public function grant($targetType, $targetValue, $permission) {
            $res = $this->find($targetType, $targetValue, $permission);
            $target = $targetType . ":" . $targetValue;
            if ($res) {
                $permId = $res->id;
                $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "permissions` SET `granted`=1 WHERE `id`='${permId}'");
            } else {
                $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "permissions` (`target`, `permission`, `granted`) VALUES ('${target}', '${permission}', '1')");
            }

            return true;
        }

        public function reject($targetType, $targetValue, $permission) {
            $res = $this->find($targetType, $targetValue, $permission);
            $target = $targetType . ":" . $targetValue;
            if ($res) {
                $permId = $res->id;
                $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "permissions` SET `granted`=0 WHERE `id`='${permId}'");
            } else {
                $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "permissions` (`target`, `permission`, `granted`) VALUES ('${target}', '${permission}', '0')");
            }

            return true;
        }

        public function clear($targetType, $targetValue = null, $permission = null) {
            $res = $this->find($targetType, $targetValue, $permission);
            if ($res) {
                $permId = $res->id;
                $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "permissions` WHERE `id`='${permId}'");
                return true;
            } else {
                return false;
            }
        }

        public function check($targetType, $targetValue, $permission, $extendedResult = false) {
            $target = $targetType . ":" . $targetValue;
            $nodes = explode('.', $permission);
            if (strpos($permission, '%') !== false) {
                $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "permissions` WHERE `target`='${target}' AND (`permission` LIKE '${permission}' OR `permission` IN ('${permission}'";
            } else {
                $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "permissions` WHERE `target`='${target}' AND `permission` IN ('${permission}'";
            }
            
            
            if (count($nodes) > 1) {
                for($i = (substr($permission, -2) == '.*' ? 1 : 0); $i < (count($nodes) - 1); $i++) {
                    $leftover = array_slice($nodes, 0, (0 - ($i + 1)));
                    $sql .= ", '" . join('.', $leftover) . ".*'";
                }
            }

            $res = $this->db->query($sql . (strpos($permission, '%') !== false ? '))' : ')'));

            if ($res->num_rows > 0) {
                $list = (array) $res->fetch_all(MYSQLI_ASSOC);
                $grantSize = 0;
                $rejectSize = 0;

                foreach($list as $item) {
                    $item = (array) $item;
                    if (intval($item['granted']) == 1 && strlen($item['permission']) > $grantSize) $grantSize = strlen($item['permission']);
                    if (intval($item['granted']) == 0 && strlen($item['permission']) > $rejectSize) $rejectSize = strlen($item['permission']);
                }

                if ($extendedResult) {
                    return (object) array(
                        "granted" => $grantSize > $rejectSize,
                        "grantSize" => $grantSize,
                        "rejectSize" => $rejectSize
                    );
                } else {
                    return $grantSize > $rejectSize;
                }
            } else {
                if ($extendedResult) {
                    return (object) array(
                        "granted" => false,
                        "grantSize" => 0,
                        "rejectSize" => 0
                    );
                } else {
                    return false;
                }
            }
        }

        public function list($input = array(), $checkWildcards = true) {
            $input = (object) $input;
            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "permissions`";

            if (count(array_keys(get_object_vars($input))) > 0) {
                $allowedFilters = array("limit", "offset", "order");

                $filters = (object) Validate::removeUnlisted($allowedFilters, $input);
                $properties = (object) Validate::removeUnlisted($this->filterAllowedProperties, $input);

                if (count(array_keys(get_object_vars($properties))) > 0) {
                    $sql .= " WHERE";
                    foreach($properties as $key => $value) {
                        if (array_keys(get_object_vars($properties))[0] !== $key) $sql .= " AND";
                        switch($key) {
                            case 'targetType':
                                $sql .= " `target` LIKE '${value}:%'";
                                break;
                            case 'targetValue':
                                $sql .= " `target` LIKE '%:${value}'";
                                break;
                            case 'granted':
                                if (is_bool($value)) $value = ($value ? 1 : 0);
                            case 'permission':
                                if ($checkWildcards) $value = $this->formatPermissionForQuery($value);
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

        public function find($targetType, $targetValue = null, $permission = null) {
            $target = $targetType . ":" . $targetValue;
            if (is_numeric($targetType) && !$targetValue && !$permission) {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "permissions` WHERE `id`='${targetType}'");
            } else {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "permissions` WHERE `target`='${target}' AND `permission`='${permission}'");
            }

            if ($res->num_rows > 0) {
                $res = (object) $res->fetch_assoc();
                $res->id = intval($res->id);
                $res->targetType = explode(':', $res->target)[0];
                $res->targetValue = intval(explode(':', $res->target)[1]);
                return $res;
            } else {
                return NULL;
            }
        }

        private function formatPermissionForQuery($permission) {
            if (substr($permission, -2) == ".*") {
                return substr($permission, -1) . '%';
            } else {
                return $permission;
            }
        }
    }