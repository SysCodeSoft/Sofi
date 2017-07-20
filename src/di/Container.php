<?php
/**
  * Sofi Web Programming Framework
  * 
  *  Service Locator 
  * 
  * @category  SofiFramework
  * @package   SofiFW
  * @author    Max Trifonov <mp.trifonov@gmail.com>
  * @copyright 2014-2015 SysCode Soft Ltd
  * @license   http://www.sofi.syscode.ru/license/ MIT Licence
  * @link      http://www.sofi.syscode.ru/ 
  */

namespace Sofi\di;

use \Sofi\base\exceptions\Exception;

/**
 * Контейнер сервисов
 * 
 * @property \SysCode\Sofi\web\http\Response  $Response
 * @property \SysCode\Sofi\web\http\Request  $Request
 * @property \SysCode\Sofi\router\Router  $Router
 * @property \SysCode\Sofi\web\Account  $Account
 * @property \SysCode\Sofi\web\Session  $Session
 * @property \SysCode\Sofi\web\Identity  $Identity
 * @property \SysCode\Sofi\web\Layout   $Layout
 * 
 * @ method \SysCode\Sofi\base\Router Router(mixed $Connect) Router
 */
class Container extends \stdClass
{
    /**
     * @var array Коллекция сервисов сингелтонов 
     */
    protected $holdServices = [];
    /**
     * @var array Коллекция фабрик сервисов 
     */
    protected $fabricServices = [];

    /**
     * Добавление сервиса создаваемого при первом запросе<br>
     * получение экземпляра осуществляется обращением к свойству
     * с именем сервиса
     * 
     * @param string $name Имя сервиса
     * @param function | string | array Описание сервиса
     * @return \SysCode\Sofi\di\ServiceLocator
     */
    public function touch($name, $function)
    {
        if (!isset($this->$name)) {
            $this->holdServices[$name] = $function;
        } else {
            $this->{$name} = $function();
        }
        return $this;
    }
    
    /**
     * Добавление фабрики сервиса<br>
     * получение нового экземпляра осуществляется вызовом метода 
     * с именем сервиса
     * 
     * @param string $name Имя сервиса
     * @param function | string | array Описание сервиса
     * @return \SysCode\Sofi\di\ServiceLocator
     */
    public function fabric($name, $function)
    {
        if (is_executable($function)) {
            $this->$name = $function;
        } else {
            throw new Exception('Bad function for Service fabric');
        }
        return $this;
    }

    /**
     * Возвращает экземпляр объекта
     * 
     * @param string $name
     * @param array $arguments
     * @return object
     */
    public function get($name, $arguments = null)
    {
        if (isset($this->holdServices[$name])) {
            $this->$name = SofiBase::createObject($this->holdServices[$name], $arguments);
            unset($this->holdServices[$name]);
            return $this->$name;
        } elseif (isset($this->$name)) {
            return SofiBase::createObject($this->$name, $arguments);
        }

        return null;
    }

    /**
     * Возвращает экземпляр объекта
     * 
     * @param string $name
     * @return object
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Возвращает 
     * 
     * @param string $name
     * @param array $arguments
     * @return object
     */
    public function __call($name, $arguments)
    {
        
    }
    
    
    
    
    public function get($class, $params = [], $config = [])
    {
        if (isset($this->_singletons[$class])) {
            // singleton
            return $this->_singletons[$class];
        } elseif (!isset($this->_definitions[$class])) {
            return $this->build($class, $params, $config);
        }

        $definition = $this->_definitions[$class];

        if (is_callable($definition, true)) {
            $params = $this->resolveDependencies($this->mergeParams($class, $params));
            $object = call_user_func($definition, $this, $params, $config);
        } elseif (is_array($definition)) {
            $concrete = $definition['class'];
            unset($definition['class']);

            $config = array_merge($definition, $config);
            $params = $this->mergeParams($class, $params);

            if ($concrete === $class) {
                $object = $this->build($class, $params, $config);
            } else {
                $object = $this->get($concrete, $params, $config);
            }
        } elseif (is_object($definition)) {
            return $this->_singletons[$class] = $definition;
        } else {
            throw new InvalidConfigException('Unexpected object definition type: ' . gettype($definition));
        }

        if (array_key_exists($class, $this->_singletons)) {
            // singleton
            $this->_singletons[$class] = $object;
        }

        return $object;
    }

    protected function getDependencies($class)
    {
        if (isset($this->_reflections[$class])) {
            return [$this->_reflections[$class], $this->_dependencies[$class]];
        }

        $dependencies = [];
        $reflection = new ReflectionClass($class);

        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    $c = $param->getClass();
                    $dependencies[] = Instance::of($c === null ? null : $c->getName());
                }
            }
        }

        $this->_reflections[$class] = $reflection;
        $this->_dependencies[$class] = $dependencies;

        return [$reflection, $dependencies];
    }

    protected function resolveDependencies($dependencies, $reflection = null)
    {
        foreach ($dependencies as $index => $dependency) {
            if ($dependency instanceof Instance) {
                if ($dependency->id !== null) {
                    $dependencies[$index] = $this->get($dependency->id);
                } elseif ($reflection !== null) {
                    $name = $reflection->getConstructor()->getParameters()[$index]->getName();
                    $class = $reflection->getName();
                    throw new InvalidConfigException("Missing required parameter \"$name\" when instantiating \"$class\".");
                }
            }
        }
        return $dependencies;
    }

    protected function build($class, $params, $config)
    {
        /* @var $reflection ReflectionClass */
        list ($reflection, $dependencies) = $this->getDependencies($class);

        foreach ($params as $index => $param) {
            $dependencies[$index] = $param;
        }

        $dependencies = $this->resolveDependencies($dependencies, $reflection);
        if (!$reflection->isInstantiable()) {
            throw new NotInstantiableException($reflection->name);
        }
        if (empty($config)) {
            return $reflection->newInstanceArgs($dependencies);
        }

        if (!empty($dependencies) && $reflection->implementsInterface('yii\base\Configurable')) {
            // set $config as the last parameter (existing one will be overwritten)
            $dependencies[count($dependencies) - 1] = $config;
            return $reflection->newInstanceArgs($dependencies);
        } else {
            $object = $reflection->newInstanceArgs($dependencies);
            foreach ($config as $name => $value) {
                $object->$name = $value;
            }
            return $object;
        }
    }

}
