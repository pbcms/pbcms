<?php
    namespace Helper;

    class Validate {
        public static function removeUnlisted($allowed, $data) {
            $data = (array) $data;
            foreach (\array_keys($data) as $key) if (!\in_array($key, (array) $allowed)) unset($data[$key]);
            return $data;
        }

        public static function listMissing($required, $data) {
            $missing = [];
            foreach ($required as $requirement) if (!\property_exists((object) $data, $requirement)) $missing[] = $requirement;
            return $missing;
        }

        public static function trimObject($data) {
            $data = (object) $data;
            foreach($data as $key => $value) $data->{$key} = trim($value);
            return $data;
        }

        public static function trimArray($data) {
            $data = (object) $data;
            foreach($data as $key => $value) $data[$key] = trim($value);
            return (array) $data;
        }

        public static function secureRequest() {
            return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
        }
    }