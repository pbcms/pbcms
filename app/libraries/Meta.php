<?php
    namespace Library;

    use \Registry\Event;

    class Meta {
        private $meta = array(
            "charset" => "UTF-8",
            "viewport" => "width=device-width, initial-scale=1, minimum-scale=1",
            "robots" => (SITE_INDEXING ? "index, follow" : "noindex, nofollow"),
            
            "url" => SITE_LOCATION,
            "og:url" => SITE_LOCATION,

            "title" => SITE_TITLE,
            "og:title" => SITE_TITLE,
            "og:site_name" => SITE_TITLE,
            "twitter:title" => SITE_TITLE,

            "description" => SITE_DESCRIPTION,
            "og:description" => SITE_DESCRIPTION,
            "twitter:description" => SITE_DESCRIPTION,
        );

        public function __construct($batch = array()) {
            $eventResult = Event::trigger("meta_class_initiated", $this->meta);
            foreach($eventResult as $eventBatch) {
                if (is_array($eventBatch)) $this->batch($eventBatch);
            }

            $this->batch($batch);
        }

        public function batch($batch = array()) {
            foreach($batch as $tag => $value) {
                if (!$value) {
                    $this->delete($tag);
                } else {
                    $this->set($tag, $value);
                }
            }
        }

        public function set($tag, $value) {
            $tag = strtolower($tag);
            switch($tag) {
                case 'description':
                    $this->meta['description'] = $this->meta['og:description'] = $this->meta['twitter:description'] = $value;
                    break;
                case 'description:social':
                    $this->meta['og:description'] = $this->meta['twitter:description'] = $value;
                    break;
                case 'description:basic':
                    $this->meta['description'] = $value;
                    break;

                case 'title':
                    $this->meta['title'] = $this->meta['og:title'] = $this->meta['twitter:title'] = $value;
                    break;
                case 'title:social':
                    $this->meta['og:title'] = $this->meta['twitter:title'] = $value;
                    break;
                case 'title:basic':
                    $this->meta['title'] = $value;
                    break;

                case 'image': 
                    $this->meta['og:image'] = $this->meta['twitter:image'] = $value;
                    break;

                case 'url':
                    $this->meta['url'] = $this->meta['og:url'] = $value;
                    break;
                case 'url:basic':
                    $this->meta['url'] = $value;
                    break;

                default:
                    $this->meta[$tag] = $value;
                    break;
            }
        }

        public function get($tag = NULL) {
            if ($tag == NULL) return $this->meta;
            return (isset($this->meta[$tag]) ? $this->meta[$tag] : NULL);
        }

        public function delete($tag) {
            if (isset($this->meta[$tag])) unset($this->meta[$tag]);
        }

        public function generate($meta = NULL) {
            if ($meta == NULL) {
                $eventResult = Event::trigger("meta_class_generation", $this->meta);
                foreach($eventResult as $eventBatch) {
                    if (is_array($eventBatch)) $this->batch($eventBatch);
                }

                $meta = $this->meta;
            }

            $generated = "";
            foreach($meta as $tag => $value) {
                if ($tag == 'charset') {
                    $generated .= "<meta charset=\"${value}\">";
                } else if ($tag == 'title') {
                    $generated .= "<title>${value}</title>";
                } else {
                    $generated .= "<meta name=\"${tag}\" content=\"${value}\">";
                }
            }

            return $generated;
        }
    }