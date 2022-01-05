<?php
    namespace Library;

    use Registry\Action;
    use Helper\Validate as Validator;

    class Roles {
        private $db = NULL;
        private $allowed = array("name", "description", "weight");
        private $filterAllowedProperties = array("id", "name", "description", "weight", "created", "updated");

        public function __construct() {
            $this->db = new Database;
        }

        public function create($name, $description, $weight = null) {
            if ($this->find($name) == NULL) {
                if (!$weight) {
                    $lastEntry = $this->db->query("SELECT `weight` FROM `" . DATABASE_TABLE_PREFIX . "roles` ORDER BY `weight` DESC LIMIT 1");
                    $weight = intval($lastEntry->fetch_assoc()['weight']) + 1;
                    if (!$weight) $weight = 1;
                }

                $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "roles` SET `weight` = `weight` + 1 WHERE `weight` >= ${weight} ORDER BY `weight` DESC");
                $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "roles` (`name`, `description`, `weight`) VALUES ('${name}', '${description}', '${weight}')");
                return true;
            } else {
                return false;
            }
        }

        public function find($identifier, $byIdAllowed = true) {
            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "roles` WHERE `name`='${identifier}'";
            if ($byIdAllowed) {
                $sql .= " OR `id`='${identifier}'";
            }

            $res = $this->db->query($sql);
            if ($res->num_rows > 0) {
                $res = (object) $res->fetch_assoc();
                $res->id = intval($res->id);
                $res->weight = intval($res->weight);
                return $res;
            } else {
                return NULL;
            }
        }

        public function list($input = array()) {
            $input = (object) $input;
            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "roles`";

            if (count(array_keys(get_object_vars($input))) > 0) {
                $allowedFilters = array("limit", "offset", "order");

                $filters = (object) Validator::removeUnlisted($allowedFilters, $input);
                $properties = (object) Validator::removeUnlisted($this->filterAllowedProperties, $input);

                if (count(array_keys(get_object_vars($properties))) > 0) {
                    $sql .= " WHERE";
                    foreach($properties as $key => $value) {
                        if (array_keys(get_object_vars($properties))[0] !== $key) $sql .= " AND";
                        $sql .= " `${key}`='${value}'";
                    }
                }

                if (isset($filters->limit)) $sql .= " LIMIT " . $filters->limit;
                if (isset($filters->offset)) $sql .= " OFFSET " . $filters->offset;
                if (isset($filters->order)) $sql .= " ORDER BY `weight` " . (strtolower($filters->order) == 'desc' ? "DESC" : "ASC");
            }

            $res = $this->db->query($sql);
            if ($res->num_rows > 0) {
                return (array) $res->fetch_all(MYSQLI_ASSOC);
            } else {
                return array();
            }
        }

        public function update($role, $changes) {
            $role = $this->find($role);
            if ($role) {
                $changes = (object) Validator::removeUnlisted($this->allowed, $changes);
                if (count(array_keys((array) $changes)) > 0) {
                    if (isset($changes->name)) {
                        if ($this->find($changes->name, false) != NULL) {
                            return (object) array(
                                "success" => false,
                                "error" => "user_exists",
                                "message" => "A user with the provided E-mail address already exists."
                            );
                        }
                    }

                    if (isset($changes->weight) && $changes->weight !== $role->weight) {
                        $moveWeight = $changes->weight + 1;
                        $newWeight = $changes->weight;
                        $oldWeight = $role->weight;
                        $roleId = $role->id;
                        if ($newWeight > $oldWeight) {
                            $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "roles` SET `weight` = `weight` + 1 WHERE `weight` >= ${moveWeight} ORDER BY `weight` DESC");
                            $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "roles` SET `weight` = '${moveWeight}' WHERE `id`='${roleId}'");
                            $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "roles` SET `weight` = `weight` - 1 WHERE `weight` >= ${oldWeight} ORDER BY `weight` ASC");
                        } else {
                            $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "roles` SET `weight` = `weight` + 1 WHERE `weight` >= ${newWeight} ORDER BY `weight` DESC");
                            $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "roles` SET `weight` = '${newWeight}' WHERE `id`='${roleId}'");
                            $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "roles` SET `weight` = `weight` - 1 WHERE `weight` > ${oldWeight} ORDER BY `weight` ASC");
                        }
                    }

                    $sql = "UPDATE `" . DATABASE_TABLE_PREFIX . "roles` SET";
                    foreach($changes as $key => $value) {
                        if (array_keys((array) $changes)[0] != $key) $sql .= ",";
                        $sql .= " `$key`='$value'";
                    }

                    $sql .= " `updated`=CURRENT_TIMESTAMP() WHERE `id`=" . $role->id;
                    $res = $this->db->query($sql);
                    return (object) array(
                        "success" => true
                    );
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "no_changes",
                        "message" => "No (valid) changes have been provided and made."
                    );
                }
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_role",
                    "message" => "De gegeven gebruiker bestaat niet."
                );
            }
        }

        public function delete($role) {
            $role = $this->find($role);
            if ($role) {
                $weight = $role->weight;
                $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "roles` WHERE `id`=" . $role->id);
                $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "roles` SET `weight` = `weight` - 1 WHERE `weight` > ${weight} ORDER BY `weight` ASC");
                return (object) array(
                    "success" => true
                );
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_user",
                    "message" => "De gegeven gebruiker bestaat niet."
                );
            }
        }

        public function getId($identifier, $byIdAllowed = true) {
            $sql = "SELECT `id` FROM `" . DATABASE_TABLE_PREFIX . "roles` WHERE `name`='${identifier}'";
            if ($byIdAllowed) {
                $sql .= " OR `id`='${identifier}'";
            }

            $res = $this->db->query($sql);
            if ($res->num_rows > 0) {
                return intval($res->fetch_assoc()['id']);
            } else {
                return NULL;
            }
        }
    }

    class RolePermissions {
        private $roles;
        private $permissions;

        public function __construct() {
            $this->roles = new Roles;
            $this->permissions = new \Library\Permissions;
        }

        public function grant($role, $permission) {
            $id = $this->roles->getId($role);
            if (!$id) return false;
            return $this->permissions->grant("role", $id, $permission);
        }

        public function reject($role, $permission) {
            $id = $this->roles->getId($role);
            if (!$id) return false;
            return $this->permissions->reject("role", $id, $permission);
        }

        public function clear($role, $permission) {
            $id = $this->roles->getId($role);
            if (!$id) return false;
            return $this->permissions->clear("role", $id, $permission);
        }

        public function check($role, $permission, $extendedResult = false) {
            $id = $this->roles->getId($role);
            if (!$id) return false;
            return $this->permissions->check("role", $id, $permission, $extendedResult);
        }

        public function list($input, $checkWildcards = true) {
            if (is_numeric($input) || is_string($input)) {
                $id = $this->roles->getId($input);
                if (!$id) return false;
                return $this->permissions->list(array(
                    "target" => "role:" . $id
                ));
            } else {
                if (!isset($input['target']) && !isset($input['targetType'])) $input['targetType'] = 'role';
                return $this->permissions->list($input, $checkWildcards);
            }
        }

        public function find($role, $permission) {
            $id = $this->roles->getId($role);
            if (!$id) return false;
            return $this->permissions->find("role", $id, $permission);
        }
    }

    Action::register('external_permission_validator:roles', function($target_type, $target_value, $permission, $extended_result = false) {
        $perms = new RolePermissions();
        return $perms->check($target_value, $permission, $extended_result);
    });

    Action::register('external_permission_list:roles', function($input, $checkWildcards = true) {
        $perms = new RolePermissions();
        return $perms->list($input, $checkWildcards);
    });