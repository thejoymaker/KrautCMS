<?php
declare(strict_types=1);
namespace Kraut\Model;

use Kraut\Util\ArrayUtil;

class Manifest {
    //  manifest.json
    // {
    //     "name": "Kraut CMS",
    //     "cms_version": "1.0.0",
    //     "type": "cms",
    //     "license": "MIT",
    //     "required": {
    //         "php_version": "8.0.0",
    //         "php_modules": {
    //             "json": "*"
    //         }
    //     }
    // }
    private array $manifestData;

    public static function __set_state($array) 
    {
        return new self($array);
    }
    
    public function __construct($input) {
        if(is_string($input) && is_file($input)) {
            $this->manifestData = json_decode(file_get_contents($input), true);
        } else if(is_array($input)) {
            $this->manifestData = $input;
        } else {
            throw new \Exception('Invalid input');
        }
        // $manifestData = json_decode(file_get_contents($filePath), true);
        // $this->name = $manifestData['name'];
        // $this->cms_version = $manifestData['cms_version'];
        // $this->type = $manifestData['type'];
        // $this->license = $manifestData['license'];
        // $this->requiredPhpVersion = $manifestData['required']['php_version'];
        // $this->requiredPhpModules = $manifestData['required']['php_modules'];
    }

    public function getName() : string {
        return $this->manifestData['name'];
    }

    public function getCmsVersion() : string {
        // return $this->cms_version;
        return $this->manifestData['cms_version'];
    }

    public function getType() : string {
        // return $this->type;
        return $this->manifestData['type'];
    }

    public function getLicense() : string {
        // return $this->license;
        return $this->manifestData['license'];
    }

    public function getRequiredPhpVersion() : ?string {
        // return $this->requiredPhpVersion;
        return $this->manifestData['required']['php_version'];
    }

    public function getRequiredPhpModules() : array {
        // return $this->requiredPhpModules;
        // return $this->manifestData['required']['php_modules'];
        $key = 'required.php_modules';
        return $this->get($key);
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
    private function get(string $key) : mixed {
        try {
            return ArrayUtil::unpack($key, $this->manifestData);
        } catch (\Exception $e) {
            return null;
        }
    }
}
?>