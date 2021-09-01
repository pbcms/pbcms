<?php
    namespace Command;

    class Clear {
        public function execute() {
            \Core::Print(chr(27).chr(91).'H'.chr(27).chr(91).'J');
        }
    }