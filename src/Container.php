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

namespace SysCode\Sofi\di;

use SysCode\Sofi\SofiBase;
use SysCode\Sofi\base\exceptions\Exception;

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

}
