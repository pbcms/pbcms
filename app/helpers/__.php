<?php
    namespace Helper;

    /**
     * Generate a random UUID v4
     * 
     * @return string
     */

    function uuidv4(): string {
        if (function_exists('com_create_guid') === true)
            return trim(com_create_guid(), '{}');

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Convert a string like 2MB to bytes.
     * Props to: https://stackoverflow.com/a/11807179
     * 
     * @param   string      $from               Input value. Example would be 1GB, 2MB, etc.
     * @return  int|null
     */
    function convertToBytes(string $from): ?int {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $number = substr($from, 0, -2);
        $suffix = strtoupper(substr($from,-2));
    
        //B or no suffix
        if (is_numeric(substr($suffix, 0, 1))) return preg_replace('/[^\d]/', '', $from);
        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) return null;    
        return $number * (1024 ** $exponent);
    }

    /**
     * Recurcively copy a directory to another destination.
     * Props to: https://stackoverflow.com/a/7775949
     * 
     * @param   string      $source             Source directory to copy files from.
     * @param   string      $destination        Destination directory to copy the files to.
     * @param   bool        $overwrite=true     Should existing files in the destination or the destination itself be overwritten?
     */
    function copyRecursive(string $source, string $destination, bool $overwrite = true): bool {
        $destination = (substr($destination, -1) == '/' ? $destination : $destination . '/');
        $success = true;
        if (!file_exists($destination) || !is_dir($destination)) {
            if (!is_dir($destination)) unlink($destination);
            if (!mkdir($destination, 0775)) $success = false;
        }
        
        foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item ) {
            $target = $destination . $iterator->getSubPathname();
            if ($item->isDir()) {
                if (!file_exists($target) || !is_dir($target)) {
                    if (file_exists($target) && !is_dir($target)) unlink($target);
                    if (!mkdir($target, 0775)) $success = false;
                }
            } else {
                if (file_exists($target) && $overwrite) unlink($target);
                if (!copy($item, $target)) $success = false;
            }
        }

        return $success;
    }

    /**
     * Formats a string in the following format: cool-string-over-here --> CoolStringOverHere.
     * 
     * @param   string      $str                Input string, example:  cool-string-over-here
     * @return  string                          Output string, example: CoolStringOverHere
     */
    function prepareFunctionNaming(string $str): string {
        $str = str_replace('-', ' ', $str);
        $str = ucwords($str);
        $str = str_replace(' ', '', $str);
        return $str;
    }