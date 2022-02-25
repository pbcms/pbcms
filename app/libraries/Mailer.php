<?php
    namespace Library;

    use Registry\Action;
    use Registry\Event;
    use Library\Policy;

    class Mailer {
        public function send($options = array()) {
            $options = (array) $options;
            $results = Event::trigger("pre-send-mail", $options);
            foreach($results as $result) {
                foreach($result as $key => $value) {
                    switch($key) {
                        case 'to':
                        case 'recipient':
                            $options['recipient'] = $value;
                            break;
                        case 'subject':
                            $options['subject'] = $value;
                            break;
                        case 'message':
                            $options['message'] = $value;
                            break;
                        case 'headers':
                            $options['headers'] = $value;
                            break;
                        case 'params':
                        case 'parameters':
                            $options['params'] = $value;
                            break;
                        default:
                            $options[$key] = $value;
                    }
                }
            }

            if (!isset($options['recipient'])) return (object) array(
                "success" => false,
                "error" => "missing_recipient"
            );

            if (!isset($options['subject'])) $options['subject'] = "No subject";
            if (!isset($options['message'])) $options['message'] = "No message";
            if (!isset($options['headers'])) $options['headers'] = array();
            if (!isset($options['params'])) $options['params'] = "";

            if (Action::exists('send-mail')) {
                return Action::call('send-mail', $options);
            } else {
                $policy = new Policy;
                if (!isset($options['headers']['From'])) $options['headers']['From'] = $policy->get('site-email');
                if (!isset($options['headers']['Reply-To'])) $options['headers']['Reply-To'] = $policy->get('site-email');
                if (!isset($options['headers']['X-Mailer'])) $options['headers']['X-Mailer'] = 'PHP/' . phpversion();
                $result = \mail($options['recipient'], $options['subject'], $options['message'], $options['headers'], $options['params']);
                if ($result) {
                    return (object) array(
                        "success" => true,
                        "result" => $result
                    );
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "unknown_error",
                        "message" => "The message was not sent due to an unknown error.",
                        "result" => $result
                    );
                }
            }
        }
    }