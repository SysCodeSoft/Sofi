<?php

namespace Sofi;

use Sofi\Base\interfaces\InitializedInterface;

/**
 * Приложение
 * 
 * @property \Sofi\HTTP\message\Request   $Request
 * @property \Sofi\Router\Router          $Router
 * @property \Sofi\mvc\Layout             $Layout
 * @property \Sofi\HTTP\message\Response  $Response
 * 
 */
class Application implements InitializedInterface
{

    protected $components = [];
    protected $modules = [];

    public function init($params = [])
    {
        if (!empty($params['components']) && is_array($params['components'])) {
            foreach ($params['components'] as $name => $param) {
//                echo $name.'<br>';
                $this->registerComponent($name, $param);
            }
        }
        
        if (!empty($params['modules']) && is_array($params['modules'])) {
            foreach ($params['modules'] as $name => $param) {
                $this->registerModule($name, $param);
            }
        }
    }

    public function registerComponent($name, $params)
    {        
        if (!empty($params['init'])) {
            unset($params['init']);
            
            $this->components[$name] = $params;
            $temp = $this->{$name};
        } else {
            $this->components[$name] = $params;
        }
    }
    
    public function registerModule($name, $params)
    {
        $this->modules[$name] = Base\Sofi::createObject($params);
        $this->modules[$name]->bootstrap($this);
    }
    
    function __get($name)
    {
        if (!empty($this->components[$name])){
            if (is_object($this->components[$name])) {
                return $this->components[$name];
            } else {
                return $this->components[$name] = Base\Sofi::createObject($this->components[$name]);
            }
        }
        
        throw new Base\exceptions\Exception('Component "'.$name.'" not defined');
    }
    
    public function prepareResult($Result)
    {
        if (is_array($Result)) {
            $rs = [];
            foreach ($Result as $key => $value) {
                $rs[] = $this->prepareResult($value);
            }
            return $rs;
        } elseif (is_object($Result) && $Result instanceof Router\Route) {
            return $Result->run();
        }
    }
            
    function __call($name, $params)
    {
        if (!empty($this->modules[$name])){
            return $this->modules[$name];
        }
        
        throw new Base\exceptions\Exception('Module "'.$name.'" not defined');
    }

}
