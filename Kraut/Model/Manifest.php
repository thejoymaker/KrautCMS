<?php
declare(strict_types=1);
namespace Kraut\Model;

use Kraut\Util\ArrayUtil;

class Manifest {
    private array $manifestData;

    public static function __set_state($array) 
    {
        return new self($array['manifestData']);
    }
    
    public function __construct($input) {
        if(is_string($input) && is_file($input)) {
            $this->manifestData = json_decode(file_get_contents($input), true);
        } else if(is_array($input)) {
            $this->manifestData = $input;
        } else {
            throw new \Exception('Invalid input');
        }
    }

    public function getName() : string {
        return $this->manifestData['name'];
    }

    public function getVersion() : string {
        return $this->manifestData['version'];
    }

    public function getCmsVersion() : string {
        return $this->manifestData['cms_version'];
    }

    public function getType() : string {
        return $this->manifestData['type'];
    }

    public function getLicense() : string {
        return $this->manifestData['license'];
    }

    public function getRequiredPhpVersion() : ?string {
        return $this->manifestData['required']['php_version'];
    }

    public function getRequiredPhpModules() : ?array {
        return $this->manifestData['required']['php_modules'];
    }

    public function getPaths() : ?array {
        return $this->manifestData['paths'];
    }

    /**
     * Get an arbitrary manifest value by dot notated key.
     * 
     * for example: 'required.php_modules' will return $this->manifestData['required']['php_modules']
     * 
     * @param string $key a key in dot notation
     * 
     * @return mixed the value of the key or null if the key does not exist
     */
    public function get(string $key) : mixed {
        try {
            return ArrayUtil::unpack($key, $this->manifestData);
        } catch (\Exception $e) {
            return null;
        }
    }
}
?>