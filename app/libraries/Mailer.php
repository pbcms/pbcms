<?php
    namespace Library;

    use Registry\Action;
    use Registry\Event;
    use Library\Policy;

    class Mailer {
        public function send($recipient, $subject, $message, $headers = array(), $params = "") {
            $results = Event::trigger("pre-send-mail", $recipient, $subject, $message, $headers, $params);
            foreach($results as $key => $value) {
                switch($key) {
                    case 'to':
                    case 'recipient':
                        $recipient = $value;
                        break;
                    case 'subject':
                        $subject = $value;
                        break;
                    case 'message':
                        $message = $value;
                        break;
                    case 'headers':
                        $headers = $value;
                        break;
                    case 'params':
                    case 'parameters':
                        $params = $value;
                        break;
                }
            }

            if (Action::exists('send-mail')) {
                return Action::call('send-mail', $recipient, $subject, $message, $headers, $params);
            } else {
                $policy = new Policy;
                if (!isset($headers['From'])) $headers['From'] = $policy->get('site-email');
                if (!isset($headers['Reply-To'])) $headers['Reply-To'] = $policy->get('site-email');
                if (!isset($headers['X-Mailer'])) $headers['X-Mailer'] = 'PHP/' . phpversion();
                return \mail($recipient, $subject, $message, $headers, $params);
            }
        }
    }