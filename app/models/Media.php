<?php
    namespace Model;

    class Media {
        private $db;

        public function __construct() {
            $this->db = new \Core\Database;
            $this->policy = new \Core\Policy;
        }

        public function upload($name, $allowedMimes, $type, $owner) {
            $uuid = \Core\uuidv4();
            $owner = $this->validateUser($owner);
            $allowedMimes = (array) $allowedMimes;
            $allowedTypes = (array) explode(',', $this->policy->get('mediaTypes'));

            if ($owner == NULL) {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_user",
                    "message" => "Deze gebruiker bestaat niet."
                );
            }

            if (!in_array($type, $allowedTypes)) {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_media_type",
                    "message" => "Het gegeven media type bestaat niet."
                );
            }

            if (isset($_FILES[$name]) && !is_array($_FILES[$name]['error'])) {
                //Check for Errors
                switch ($_FILES[$name]['error']) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        return (object) array(
                            "success" => false,
                            "error" => "no_file_sent",
                            "message" => "Er is geen bestand geupload."
                        );
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        return (object) array(
                            "success" => false,
                            "error" => "exceeded_upload_limit",
                            "message" => "Het geuploadde bestand is te groot."
                        );
                    default:
                        return (object) array(
                            "success" => false,
                            "error" => "unknown_error",
                            "message" => "Er is een onbekende fout opgetreden."
                        );
                }

                //Check if file is too big
                if ($_FILES[$name]['size'] > 2*MB) {
                    return (object) array(
                        "success" => false,
                        "error" => "exceeded_filesize_limit",
                        "message" => "Het geuploadde bestand is te groot."
                    );
                }

                //Validate file type
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $ext = array_search($finfo->file($_FILES[$name]['tmp_name']), $allowedMimes, true);

                if (!$ext && count($allowedMimes) > 0) {
                    return (object) array(
                        "success" => false,
                        "error" => "invalid_file_type",
                        "message" => "Het bestandstype van het geuploadde bestand is niet toegestaan voor het geselecteerde mediatype."
                    );
                }

                $res = move_uploaded_file($_FILES[$name]['tmp_name'], 'media/' . $uuid . '.' . $ext);
                if ($res) {
                    $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "media` (`uuid`, `ext`, `type`, `owner`) VALUES ('${uuid}', '${ext}', '${type}', '${owner}')");

                    return (object) array(
                        "success" => true,
                        "uuid" => $uuid,
                        "extention" => $ext,
                        "filename" => $uuid . '.' . $ext,
                        "path" => "media/" . $uuid . '.' . $ext
                    );
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "file_not_moved",
                        "message" => "Het bestand kon niet worden opgeslagen op de server."
                    );
                }
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "invalid_parameters",
                    "message" => "Er zijn incorrecte gegevens gespecificeerd."
                );
            }
        }

        public function info($identifier = NULL) {
            if ($identifier === NULL) return false;
            $query = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "media` WHERE `id`='${identifier}' OR `uuid`='${identifier}'";
            $res = $this->db->query($query);
            if ($res->num_rows > 0) {
                $info = (object) $res->fetch_assoc();
                return (object) array(
                    "id" => $info->id,
                    "uuid" => $info->uuid,
                    "extention" => $info->ext,
                    "type" => $info->type,
                    "owner" => $info->owner,
                    "filename" => $info->uuid . '.' . $info->ext,
                    "path" => 'media/' . $info->uuid . '.' . $info->ext
                );
            } else {
                return NULL;
            }
        }

        public function list($input = array()) {
            $input = (object) $input;
            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "media`";

            if (count(array_keys(get_object_vars($input))) > 0) {
                $allowedFilters = array("limit", "offset", "order");
                $allowedProperties = array("id", "uuid", "ext", "type", "owner");

                $filters = (object) \Core\Validate::removeUnlisted($allowedFilters, $input);
                $properties = (object) \Core\Validate::removeUnlisted($allowedProperties, $input);

                if (count(array_keys(get_object_vars($properties))) > 0) {
                    $sql .= " WHERE";
                    foreach($properties as $key => $value) {
                        if (array_keys(get_object_vars($properties))[0] !== $key) $sql .= " AND";
                        $sql .= " `${key}`='${value}'";
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

        public function count($input = array()) {
            return count($this->list($input));
        }

        public function delete($identifier) {
            $media = $this->info($identifier);
            if ($media != NULL) {
                $dependencies = (object) array("num_rows" => 0);
                if ($media->type == 'profilepicture') $dependencies = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "usermeta` WHERE `name`='profilePicture' AND `value`=" . $media->id);

                if ($dependencies->num_rows > 0) {
                    return (object) array(
                        "success" => false,
                        "error" => "dependencies_not_cleared",
                        "message" => "Het opgevraagde media item is nog steeds in gebruik als \"" . $media->type . "\"."
                    );
                } else {
                    $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "media` WHERE `id`=" . $media->id);
                    return (object) array(
                        "success" => true
                    );
                }
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_media",
                    "message" => "Het opgevraagde media item bestaat niet."
                );
            }
        }

        public function getMimes($extention) {
            
        }

        private function validateUser($identifier) {
            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "users` WHERE `email`='${identifier}' OR `id`='${identifier}'";    
            $res = $this->db->query($sql);
            if ($res->num_rows > 0) {
                $user = (object) $res->fetch_assoc();
                return $user->id;
            } else {
                return NULL;
            }
        }
    }