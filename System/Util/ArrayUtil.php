<?php 

declare(strict_types=1);   

namespace Kraut\Util;


class ArrayUtil {
    
    /**
     * Unpacks a value from a nested array using a dot-notated key.
     * 
     * For example using the key 'foo.bar' would unpack the value at $array['foo']['bar'].
     *
     * @param string $key The dot-notated key to unpack the value from the array.
     * @param array $array The array to unpack the value from.
     * @return mixed The unpacked value.
     * @throws \Exception If the key is not found in the array.
     */
    public static function unpack(string $key, array $array): mixed {
        $keys = explode('.', $key);
        $value = $array;
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                throw new \Exception('Key not found in array');
            }
        }
        return $value;
    }
}

?>