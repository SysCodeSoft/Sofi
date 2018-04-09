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

use Sofi\Base;
use Sofi\Base\exceptions\Exception;

/**
 * Сервис локатор
 * 
 * @ property \Sofi\web\http\Response  $Response
 * 
 * @ method \Sofi\base\Router Router(mixed $Connect) Router
 */
class ServiceLocator extends Base\Initialized
{
    use Base\traits\Init;
    
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
     * @return \Sofi\di\ServiceLocator
     */
    public function touch($name, $function)
    {
        if (!isset($this->{$name})) {
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
     * @return \Sofi\di\ServiceLocator
     */
    public function fabric($name, $function)
    {
        if (is_executable($function)) {
            $this->{$name} = $function;
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
            $this->$name = Base\Sofi::createObject($this->holdServices[$name], $arguments);
            unset($this->holdServices[$name]);
            return $this->$name;
        } elseif (isset($this->$name)) {
            return Base\Sofi::createObject($this->$name, $arguments);
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

}
