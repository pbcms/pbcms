<?php
    namespace ModulePbMaintenance;

    class ApplyTemplate extends \Libary\Controller {
        public function __construct() {
            $this->template('pb-default', array(
                "title" => "Maintenance"
            ));
        }
    }
?>

This is the maintenance page.

<?php
    new ApplyTemplate();