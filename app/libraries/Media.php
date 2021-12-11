<?php
    namespace Library;

    use Helper\Validate as Validator;

    class Media {
        private $db;
        private $filterAllowedProperties = array("id", "uuid", "ext", "type", "owner", "public");

        public function __construct() {
            $this->db = new Database;
        }

        public function create($type, $owner, $source, $ext = null) {
            $mediaTypes = new MediaTypes;
            $type = $mediaTypes->info($type);
            if ($type) {
                if (!$ext) {
                    $explodedFileName = explode('.', $source);
                    if (count($explodedFileName) > 1) {
                        $ext = end($explodedFileName);
                    } else {
                        $ext = 'file';
                    }
                }

                if (in_array($ext, explode(',', $type->extensions))) {
                    $users = new Users;
                    $user = $users->getId($owner);
                    if ($user === NULL) {
                        return (object) array(
                            "success" => false,
                            "error" => "unknown_user",
                            "message" => "The user assigned to the media item does not exist."
                        );
                    } else if ($user < 1) {
                        return (object) array(
                            "success" => false,
                            "error" => "invalid_user",
                            "message" => "The virtual Visitor profile (user 0) cannot hold any media items."
                        );
                    } else {
                        if (file_exists($source)) {
                            $maxSize = \Helper\convertToBytes($type->maxSize);
                            if (filesize($source) > $maxSize) {
                                return (object) array(
                                    "success" => false,
                                    "error" => "exceeds_maximum_filesize",
                                    "message" => "The provided source exceeds the maximum filesize for the requested media type."
                                );
                            }

                            $uuid = \Helper\uuidv4();
                            $destination = DYNAMIC_DIR . '/media/' . $uuid . '_100.' . $ext;
                            if (copy($source, $destination)) {
                                $typeId = $type->id;
                                $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "media` (`uuid`, `ext`, `type`, `owner`) VALUES ('${uuid}', '${ext}', '${typeId}', '${user}')");
                                return (object) array(
                                    "success" => true,
                                    "uuid" => $uuid
                                );
                            } else {
                                return (object) array(
                                    "success" => false,
                                    "error" => "failed_copy",
                                    "message" => "Failed to copy the source file to the destination."
                                );
                            }
                        } else {
                            return (object) array(
                                "success" => false,
                                "error" => "invalid_file",
                                "message" => "The given filepath does not exist within the current filesystem."
                            );
                        }
                    }
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "invalid_filetype",
                        "message" => "The requested mediatype does not allow the given filetype."
                    );
                }
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_mediatype",
                    "message" => "The requested mediatype does not exist."
                );
            }
        }

        public function makePublic($query) {
            $info = $this->info($query);
            if ($info) {
                $mediaId = $info->id;
                $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "media` SET `public`='1' WHERE `id`='${mediaId}'");
                return (object) array(
                    "success" => true
                );
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_media",
                    "message" => "The requested media item does not exist."
                );
            }
        }

        public function makePrivate($query) {
            $info = $this->info($query);
            if ($info) {
                $mediaId = $info->id;
                $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "media` SET `public`='0' WHERE `id`='${mediaId}'");
                return (object) array(
                    "success" => true
                );
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_media",
                    "message" => "The requested media item does not exist."
                );
            }
        }

        public function info($query) {
            if (preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $query)) {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "media` WHERE `uuid`='${query}'");
            } else {
                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "media` WHERE `id`='${query}'");
            }
            
            if ($res->num_rows > 0) {
                $res = (object) $res->fetch_assoc();
                $res->id = intval($res->id);
                $res->type = intval($res->type);
                $res->owner = intval($res->owner);
                $res->file = $res->uuid . '_100.' . $res->ext;
                $res->path = DYNAMIC_DIR . '/media/';
                $res->filepath = DYNAMIC_DIR . '/media/' . $res->uuid . '_100.' . $res->ext;
                $res->public = (intval($res->public) == 1 ? true : false);
                return $res;
            } else {
                return NULL;
            }
        }

        public function list($input = array()) {
            $input = (object) $input;
            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "media`";

            if (count(array_keys(get_object_vars($input))) > 0) {
                $allowedFilters = array("limit", "offset", "order");

                $filters = (object) Validator::removeUnlisted($allowedFilters, $input);
                $properties = (object) Validator::removeUnlisted($this->filterAllowedProperties, $input);

                if (count(array_keys(get_object_vars($properties))) > 0) {
                    $sql .= " WHERE";
                    foreach($properties as $key => $value) {
                        if (array_keys(get_object_vars($properties))[0] !== $key) $sql .= " AND";
                        $sql .= " `$key`='${value}'";
                    }
                }

                if (isset($filters->limit)) $sql .= " LIMIT " . $filters->limit;
                if (isset($filters->offset)) $sql .= " OFFSET " . $filters->offset;
                if (isset($filters->order)) $sql .= " ORDER BY `id` " . (strtolower($filters->order) == 'desc' ? "DESC" : "ASC");
            }

            $res = $this->db->query($sql);
            if ($res->num_rows > 0) {
                $list = (array) $res->fetch_all(MYSQLI_ASSOC);
                $list = array_map(function($item) {
                    $item = (object) $item;
                    $item->id = intval($item->id);
                    $item->type = intval($item->type);
                    $item->owner = intval($item->owner);
                    $item->file = $item->uuid . '_100.' . $item->ext;
                    $item->path = DYNAMIC_DIR . '/media/';
                    $item->filepath = DYNAMIC_DIR . '/media/' . $item->uuid . '_100.' . $item->ext;
                    return $item;
                }, $list);

                return (array) $list;
            } else {
                return array();
            }
        }

        public function transfer($query, $owner) {
            $info = $this->info($query);
            if ($info) {
                $users = new Users;
                $user = $users->getId($owner);
                if ($user === NULL) {
                    return (object) array(
                        "success" => false,
                        "error" => "unknown_user",
                        "message" => "The targeted user does not exist."
                    );
                } else if ($user < 1) {
                    return (object) array(
                        "success" => false,
                        "error" => "invalid_user",
                        "message" => "The virtual Visitor profile (user 0) cannot hold any media items."
                    );
                } else {
                    $mediaId = $info->id;
                    $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "media` SET `owner`='${user}' WHERE `id`='${mediaId}'");
                    return (object) array(
                        "success" => true
                    );
                }
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_media",
                    "message" => "The requested media item does not exist."
                );
            }
        }

        public function delete($query) {
            $info = $this->info($query);
            if ($info) {
                array_map('unlink', glob($info->path . $info->uuid . '_*.' . $info->ext));
                Event::trigger("media_deleted", (object) array(
                    "id" => $info->id,
                    "uuid" => $info->uuid
                ));

                $mediaId = $info->id;
                $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "media` WHERE `id`='${mediaId}'");
                return (object) array(
                    "success" => true
                );
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_media",
                    "message" => "The requested media item does not exist."
                );
            }
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

        public function info($query, $byIdAllowed = true) {
            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "media-types` WHERE `type`='${query}'";
            if ($byIdAllowed) $sql .= " OR `id`='${query}'";
            $res = $this->db->query($sql);
            if ($res->num_rows > 0) {
                $res = $res->fetch_assoc();
                $res['maxSize'] = $res['max-size'];
                unset($res['max-size']);
                $res = (object) $res;
                $res->id = intval($res->id);
                return $res;
            } else {
                return NULL;
            }
        }

        public function update($query, $changes) {
            $info = $this->info($query);
            if ($info) {
                $changes = (object) Validator::removeUnlisted($this->allowed, $changes);
                if (count(array_keys((array) $changes)) > 0) {
                    if (isset($changes->extensions) && is_array($changes->extensions)) $changes->extensions = join(',', $changes->extensions);

                    $sql = "UPDATE `" . DATABASE_TABLE_PREFIX . "media-types` SET";
                    foreach($changes as $key => $value) {
                        if (array_keys((array) $changes)[0] != $key) $sql .= ",";
                        if ($key == 'maxSize') $key = 'max-size';
                        $sql .= " `$key`='$value'";
                    }

                    $sql .= " WHERE `id`=" . $info->id;
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

        public function delete($query, $force = false) {
            $info = $this->info($query);
            if ($info) {
                $typeId = $info->id;
                if (!$force) {
                    $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "media` WHERE `type`='${typeId}'");
                    if ($res->num_rows > 0) return (object) array(
                        "success" => false,
                        "error" => "dependencies_not_cleared",
                        "message" => "There are still media items that depend on this mediatype."
                    );
                }

                $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "media-types` WHERE `id`='${typeId}'");
                return (object) array(
                    "success" => true
                );
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_media",
                    "message" => "The requested media item does not exist."
                );
            }
        }
    }
