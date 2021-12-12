<?php
    namespace Library;

    class Assets {
        private static $permanent = array(
            "head" => array(),
            "body" => array()
        );

        private $local = array(
            "head" => array(),
            "body" => array()
        );

        public function registerBatch($target, $batch) {
            $target = strtolower($target);
            if (in_array($target, array('body', 'head'))) {
                foreach($batch as $asset) {
                    if (is_string($asset)) {
                        $this->register($target, $asset);
                    } else {
                        if (count($asset) > 0) $this->register($target, $asset[0], $asset[1], $asset[2]);
                    }
                }
            } else {
                return false;
            }
        }

        public function registerHead($type, $variable = null, $input = array()) {
            $this->register('head', $type, $variable, $input);
        }

        public function registerBody($type, $variable = null, $input = array()) {
            $this->register('body', $type, $variable, $input);
        }

        public function generateHead() {
            return $this->generate('head');
        }

        public function generateBody() {
            return $this->generate('body');
        }

        private function register($target, $type, $variable = null, $input = array()) {
            $target = strtolower($target);
            $asset = (object) array(
                "type" => $type,
                "source" => null,
                "origin" => null,
                "properties" => "",
                "permanent" => false
            );

            if (is_string($variable) || is_integer($variable)) {
                $asset->source = $variable;
            } else if (is_object($variable) || is_array($variable)) {
                $input = $variable;
            } else if (!$variable) {
                $asset->type = 'custom';
                $asset->source = $type;
            }

            if (!$input) $input = array();
            foreach($input as $key => $value) {
                switch($key) {
                    case 'origin':
                        $allowed = array('remote', 'pubfiles', 'module');
                        if (in_array(explode(':', $value)[0], $allowed)) $asset->origin = $value;
                        break;
                    case 'properties':
                        if ($value) $asset->properties = $value;
                        break;
                    case 'permanent':
                        $asset->permanent = $value;
                        break;
                    case 'source':
                        $asset->source = $value;
                        break;
                    case 'rel':
                        if ($asset->type == 'style' || $asset->type == 'css' || $asset->type == 'link') $asset->rel = $value;
                        break;
                }
            }

            if ($asset->permanent) {
                array_push(self::$permanent[$target], $asset);
            } else {
                array_push($this->local[$target], $asset);
            }
        }

        private function generateAsset($asset) {
            $asset = (object) $asset;
            $result = null;
            $obtained_source = null;
            $short_type = null;
            if ($asset->type == 'style' || $asset->type == 'css' || $asset->type == 'link') $short_type = 'css';
            if ($asset->type == 'javascript' || $asset->type == 'js' || $asset->type == 'script') $short_type = 'js';
            if ($asset->properties !== "") $asset->properties = " " . $asset->properties;

            if ($short_type) {
                switch(explode(':', $asset->origin)[0]) {
                    case 'module':
                        if (isset(explode(':', $asset->origin)[1])) {
                            $modules = new Modules;
                            $module = explode(':', $asset->origin)[1];
    
                            if ($modules->exists($module)) {                    
                                if (file_exists(DYNAMIC_DIR . '/modules/' . $module . '/static/' . $asset->source)) {
                                    $obtained_source = file_get_contents(DYNAMIC_DIR . '/modules/' . $module . '/static/' . $asset->source);
                                } else {
                                    return '<script>alert("Asset \'' . $asset->source . '\' for module \'' . $module . '\' does not exist.");</script>';
                                }
                            } else {
                                return '<script>alert("No module name provided for asset with source: \'' . $asset->source . '\'.");</script>';
                            }
                        } else {
                            return '<script>alert("Module \'' . $module . '\' does not exist for asset with source: \'' . $asset->source . '\'.");</script>';
                        }
    
                        break;
                    case 'pubfiles':
                        if (file_exists(PUBFILES_DIR . '/' . $asset->source)) {
                            $obtained_source = file_get_contents(PUBFILES_DIR . '/' . $asset->source);
                        } else {
                            if (file_exists(PUBFILES_DIR . '/' . $short_type . '/' . $asset->source)) {
                                $obtained_source = file_get_contents(PUBFILES_DIR . '/' . $short_type . '/' . $asset->source);
                            } else {
                                return '<script>alert("Asset \'' . $asset->source . '\' does not exist within pubfiles.");</script>';
                            }
                        }
                }
            }

            if ($obtained_source) {
                switch($short_type) {
                    case 'css':
                        $result = '<style' . $asset->properties . '>' . $obtained_source . '</style>';
                        break;
                    case 'js':
                        $result = '<script' . $asset->properties . '>' . $obtained_source . '</script>';
                        break;
                }
            } else {
                switch($asset->type) {
                    case 'custom':
                    case 'plain':
                        $result = $asset->source;
                        break;
                    case 'script':
                    case 'javascript':
                    case 'js':
                        if (filter_var($asset->source, FILTER_VALIDATE_URL) || $asset->origin == 'remote') { 
                            $result = '<script src="' . $asset->source . '"' . $asset->properties . '></script>';
                        } else {
                            $result = '<script' . $asset->properties . '>' . $asset->source . '</script>';
                        }

                        break;
                    case 'style':
                    case 'link':
                    case 'css':
                        if (filter_var($asset->source, FILTER_VALIDATE_URL) || $asset->origin == 'remote') { 
                            $result = '<link rel="' . (isset($asset->rel) ? $asset->rel : 'stylesheet') . '" href="' . $asset->source . '"' . $asset->properties . '>';
                        } else {
                            $result = '<style' . $asset->properties . '>' . $asset->source . '</script>';
                        }
                        
                        break;

                    default:
                        $result = '<script>alert("Asset with unknown type \'' . $asset->type . '\' was not loaded.");</script>';
                }
            }

            return $result;
        }

        private function generate($type) {
            $result = '';
            foreach(self::$permanent[$type] as $asset) $result .= $this->generateAsset($asset);
            foreach($this->local[$type] as $asset) $result .= $this->generateAsset($asset);
            return $result;
        }
    }