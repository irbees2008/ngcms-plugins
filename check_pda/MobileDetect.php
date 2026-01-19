<?php

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use function Plugins\{logger, cache_get, cache_put, get_ip};

require_once 'lib/Mobile_Detect.php';

class Twig_Extension_MobileDetect extends AbstractExtension
{
    protected $detector;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->detector = new Mobile_Detect();

        // Log device detection
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $isMobile = $this->detector->isMobile();
        $isTablet = $this->detector->isTablet();
        $deviceType = $isTablet ? 'tablet' : ($isMobile ? 'mobile' : 'desktop');

        logger('check_pda', 'Device detected: ' . $deviceType . ', IP=' . get_ip() . ', UA=' . substr($userAgent, 0, 100));
    }

    /**
     * Twig functions
     *
     * @return array
     */
    public function getFunctions()
    {
        $functions = [
            new TwigFunction('get_available_devices', [$this, 'getAvailableDevices']),
            new TwigFunction('is_mobile', [$this, 'isMobile']),
            new TwigFunction('is_tablet', [$this, 'isTablet']),
        ];

        foreach ($this->getAvailableDevices() as $device => $fixedName) {
            $methodName = 'is' . $device;
            $twigFunctionName = 'is_' . $fixedName;
            $functions[] = new TwigFunction($twigFunctionName, [$this, $methodName]);
        }

        return $functions;
    }

    /**
     * Returns an array of all available devices
     *
     * @return array
     */
    public function getAvailableDevices()
    {
        // Cache available devices list
        $cacheKey = 'check_pda_available_devices';
        $cached = cache_get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $availableDevices = array();
        $rules = array_change_key_case($this->detector->getRules());

        foreach ($rules as $device => $rule) {
            $availableDevices[$device] = static::fromCamelCase($device);
        }

        // Cache for 24 hours (devices list doesn't change often)
        cache_put($cacheKey, $availableDevices, 86400);
        logger('check_pda', 'Available devices list cached: ' . count($availableDevices) . ' devices');

        return $availableDevices;
    }

    /**
     * Pass through calls of undefined methods to the mobile detect library
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->detector, $name), $arguments);
    }

    /**
     * Converts a string to camel case
     *
     * @param $string
     * @return mixed
     */
    protected static function toCamelCase($string)
    {
        return preg_replace('~\s+~', '', lcfirst(ucwords(strtr($string, '_', ' '))));
    }

    /**
     * Converts a string from camel case
     *
     * @param $string
     * @param string $separator
     * @return string
     */
    protected static function fromCamelCase($string, $separator = '_')
    {
        return strtolower(preg_replace('/(?!^)[[:upper:]]+/', $separator . '$0', $string));
    }

    /**
     * The extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'mobile_detect.twig.extension';
    }
}
