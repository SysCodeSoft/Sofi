<?php

namespace Sofi;

use Sofi\Base\Sofi;
use Sofi\Base\interfaces\InitializedInterface;

/**
 * Модуль
 * 
 * @property \Sofi\Application  $App
 */
class Module implements InitializedInterface
{
    use Base\traits\Init;
    
    /**
     *
     * @var \Sofi\Application;
     */
    protected $App;
    
    protected $moduleName = '';
    protected $moduleNameSpace = '';
    protected $modulePath = '';
            
    function __construct()
    {
        $path = explode('\\',static::class);
        $this->moduleName = end($path);
        unset($path[key($path)]);
        $this->moduleNameSpace = implode('\\', $path);        
    }
    
    function bootstrap($app)
    {
        $this->App = $app;
    }

}
