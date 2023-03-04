<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-08 15:37:42
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Map.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

// Loading layer record all routing information configured by the system

use \core\Input;

class Map
{
    protected string $className;
    protected $functionName;
    protected string|false $controllerName;
    protected array $params = array();

    public function __construct($className, $functionName, $params = array())
    {
        $this->className = $className;
        $this->functionName = $functionName;
        $this->params = $params;
        if (($index = strrpos($className, '\\')) !== false) {
            $this->controllerName = substr($className, $index + 1);
        } else {
            $this->controllerName = $className;
        }
    }

    public static function create($className, $functionName, $params = array()): Map
    {
        return new self($className, $functionName, $params);
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function run($arguments = null)
    {
        if ($arguments === null) {
            $params = array();
            foreach ($this->params as $item)
                $params[] = Input::get($item);
        } else {
            $params = $arguments;
        }

        Log::record(array(
            'CLASS' => $this->className,
            'ACTION' => is_callable($this->functionName) ? 'function' : $this->functionName,
            'PARAM' => json_encode($this->params),
        ));

        return call_user_func_array($this->className ? [new $this->className, $this->functionName] : $this->functionName, $params);
    }
}
