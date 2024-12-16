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

    /**
     * Packs a value into a nested array using a dot-notated key.
     * 
     * For example using the key 'foo.bar' would pack the value into $array['foo']['bar'].
     *
     * @param string $key The dot-notated key to pack the value into the array.
     * @param mixed $value The value to pack into the array.
     * @param array $array The array to pack the value into.
     * @return array The array with the packed value.
     */
    public static function pack(string $key, mixed $value, array &$array): array {
        $keys = explode('.', $key);
        $tmp = &$array;
        foreach ($keys as $k) {
            if (!isset($tmp[$k])) {
                $tmp[$k] = [];
            }
            $tmp = &$tmp[$k];
        }
        $tmp = $value;
        return $array;
    }

    /**
     * Recursively traverses an array and generates a list of dot notated keys.
     * 
     * @param array $array The array to parse.
     * @param string $prefix The prefix to prepend to the keys.
     * @param array $result The array to store the dot notated keys.
     * @return void
     */
    public static function parseKeys(array $array, string $prefix, array &$result): void
    {
        foreach ($array as $key => $value) {
            if(is_null($prefix) || $prefix === '') {
                $newKey = $key;
            } else {
                $newKey = $prefix . '.' . $key;
            }
            $newKey = $prefix . '.' . $key;
            if (is_array($value)) {
                self::parseKeys($value, $newKey, $result);
            } else {
                $result[] = $newKey;
            }
        }
    }
}

?>