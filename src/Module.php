<?php

namespace Sofi;

use Sofi\Base\Sofi;
use Sofi\Base\interfaces\InitializedInterface;

/**
 * Модуль
 * 
 * @property \Sofi\Application  $App
 */
class Module  implements InitializedInterface
{
    use \Sofi\Base\traits\Init;
    
    /**
     *
     * @var \Sofi\Application;
     */
    protected $App = null;
    protected $Owner = null;
    
    protected $moduleName = '';
    protected $moduleNameSpace = '';
    protected $modulePath = '';
            
    function __construct($Owner = null)
    {
        $path = explode('\\',static::class);
        $this->moduleName = end($path);
        unset($path[key($path)]);
        $this->moduleNameSpace = implode('\\', $path);        
        $this->Owner = $Owner;
    }
    
    function bootstrap(\Sofi\Application $App = null)
    {
        $this->App = $App;
    }

}
