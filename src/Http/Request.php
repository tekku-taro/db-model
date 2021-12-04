<?php

namespace Taro\DBModel\Http;

class Request
{
    private $get;

    private $post;

    private $server;

    /** @var Request $request */
    private static $request;

    function __construct($requestData = [])
    {
        foreach ($requestData as $key => $value) {
            if(property_exists($this, $key)) {
                $this->{$key} = $value;
            }            
        }
        self::$request = $this;
    }

    public static function create($requestData = [])
    {
        $request = new Request($requestData);
        
        return $request;
    }

    public static function getInstance()
    {
        if(!isset(self::$request)) {
            return self::create();
        }
        return self::$request;
    }

    public function get($key, $default = null)
    {
        return $this->getValueFrom('get', $key, $default);
    }

    public function post($key, $default = null)
    {
        return $this->getValueFrom('post', $key, $default);
    }

    public function server($key, $default = null)
    {
        return $this->getValueFrom('server', $key, $default);
    }

    public function getValueFrom($property, $key, $default = null)
    {
        if(isset($this->{$property}[$key])) {
            return $this->{$property}[$key];
        }

        return $default;
    }



    public function input($key, $default = null)
    {
        $data = $this->post($key);
        if($data === null) {
            $data = $this->get($key);
        }
        if($data === null) {
            return $default;
        }

        return $data;
    }

    public function all()
    {
        return $this->post + $this->get;
    }

}