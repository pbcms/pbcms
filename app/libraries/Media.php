<?php
    namespace Library;

    use Helper\Validate as Validator;

    class Media {
        public function create() {
            
        }
    }

    class MediaTypes {
        private $db;
        private $allowed = array("extensions", "maxSize");
        private $filterAllowedProperties = array("id", "type", "extensions", "maxSize");

        public function __construct() {
            $this->db = new Database;
        }

        public function exists($query) {
            $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "media-types` WHERE `id`='${query}' OR `type`='${query}'");
            return $res->num_rows > 0;
        }

        public function create($type, $extensions, $maxSize) {
            if (is_array($extensions)) $extensions = join(',', $extensions);
            if (strval(intval($type)) == $type) return false;   
            if ($this->exists($type)) return false;
            $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "media-types` (`type`, `extensions`, `max-size`) VALUES ('${type}', '${extensions}', '${maxSize}')");
            return true;
        }

        public function list($input = array()) {
            $input = (object) $input;
            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "media-types`";

            if (count(array_keys(get_object_vars($input))) > 0) {
                $allowedFilters = array("limit", "offset", "order");

                $filters = (object) Validator::removeUnlisted($allowedFilters, $input);
                $properties = (object) Validator::removeUnlisted($this->filterAllowedProperties, $input);

                if (isset($properties->extensions)) {
                    if (is_string($properties->extensions)) $properties->extensions = explode(',', $properties->extensions);
                    $properties->extensions = array_map(function($ext) {
                        return "(`extensions` LIKE '%${ext},%' OR `extensions` LIKE '%,${ext}%' OR `extensions`='${ext}')";
                    }, $properties->extensions);
                    $properties->extensions = join(' AND ', $properties->extensions);
                }

                if (count(array_keys(get_object_vars($properties))) > 0) {
                    $sql .= " WHERE";
                    foreach($properties as $key => $value) {
                        if (array_keys(get_object_vars($properties))[0] !== $key) $sql .= " AND";
                        if ($key == 'extensions') {
                            $sql .= " ${value}";
                        } else {
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

        public function info($query) {
            $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "media-types` WHERE `id`='${query}' OR `type`='${query}'");
            if ($res->num_rows > 0) {
                $res = (object) $res->fetch_assoc();
                $res->id = intval($res->id);
                return $res;
            } else {
                return NULL;
            }
        }

        public function update($query, $updates) {
            if (is_array($extensions)) $extensions = join(',', $extensions);
            $info = $this->find($query);
            if ($media) {
                $changes = (object) Validator::removeUnlisted($this->allowed, $changes);
                if (count(array_keys((array) $changes)) > 0) {
                    $sql = "UPDATE `" . DATABASE_TABLE_PREFIX . "media-types` SET";
                    foreach($changes as $key => $value) {
                        if (array_keys((array) $changes)[0] != $key) $sql .= ",";
                        $sql .= " `$key`='$value'";
                    }

                    $sql .= " WHERE `id`=" . $media->id;
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
                    "error" => "unknown_mediatype",
                    "message" => "The given mediatype does not exist."
                );
            }
        }
    }
