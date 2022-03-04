<?php
    namespace Helper;

    class Random {
        
        /**
         * Source: https://stackoverflow.com/a/4356295
         */
        public static function String($length = 10) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        public static function Number($min = PHP_INT_MIN, $max = PHP_INT_MAX) {
            try {
                $r = random_int($min, $max);
            } catch(Exception $e) {
                $r = rand($min, $max);
            }

            return $r;
        }
    }