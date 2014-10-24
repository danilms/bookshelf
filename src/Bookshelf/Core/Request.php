<?php
/**
 * This code belongs to of Opensoft company
 */

namespace Bookshelf\Core;

/**
 * @author Danil Vasiliev <danil.vasiliev@opensoftdev.ru>
 */
class Request
{
    const GET_METHOD = 'GET';
    const POST_METHOD = 'POST';

    /**
     * @param string null $key
     * @param string null $value
     */
    public $data;

    /**
     * @param array $getArray
     * @param array $postArray
     */
    public function __construct($getArray, $postArray)
    {
        $this->data = array_merge($getArray, $postArray);
    }

    /**
     * @param string $key
     * @param string null $default
     * @return string
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->data)) {
            $default = $this->data[$key];
        }

        return $default;
    }

    /**
     * @param array $requestArray
     */
    public function set($requestArray)
    {
        foreach ($requestArray as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD']) === self::GET_METHOD) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD']) === self::POST_METHOD) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @return string
     */
    public function getMethod()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD']) === self::POST_METHOD) {
            return self::POST_METHOD;
        } else if (strtoupper($_SERVER['REQUEST_METHOD']) === self::GET_METHOD) {
            return self::GET_METHOD;
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasKey($key)
    {
        if (array_key_exists($key, $this->data)){
            return true;
        } else {
            return false;
        }
    }
}
