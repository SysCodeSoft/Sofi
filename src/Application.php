<?php

namespace Sofi;

use Sofi\Base\interfaces\InitializedInterface;

/**
 * Приложение
 * 
 * @property \Sofi\Router\Router          $Router
 * @property \Sofi\data\DB\interfaces\DB  $DB
 * @property \Sofi\mvc\Layout             $Layout
 * @property \app\Services                $Services
 * @property \app\components\UserContext  $UserContext
 * 
 */
class Application extends Module
{

    protected $name;
    protected $components = [];
    protected $modules = [];

    public function init($params = [])
    {
        if (!empty($params['components']) && is_array($params['components'])) {
            foreach ($params['components'] as $name => $param) {
                $this->registerComponent($name, $param);
            }

            unset($params['components']);
        }

        if (!empty($params['modules']) && is_array($params['modules'])) {
            foreach ($params['modules'] as $name => $param) {
                $this->registerModule($name, $param);
            }
            unset($params['modules']);
        }

        parent::init($params);
    }

    public function registerComponent($name, $params)
    {
        if (is_array($params) && !empty($params['init'])) {
            unset($params['init']);

            $this->components[$name] = $params;
            $temp = $this->{$name};
        } else {
            $this->components[$name] = $params;
        }
    }

    public function registerModule($name, $class, $params = [])
    {
        $this->modules[$name] = Base\Sofi::createObject($class, [$this], $params);
        $this->modules[$name]->bootstrap($this);
    }

    function __get($name)
    {
        if (!empty($this->components[$name])) {
            if (is_object($this->components[$name])) {
                return $this->components[$name];
            } else {
                return $this->components[$name] = Base\Sofi::createObject($this->components[$name], null, []);
            }
        }

        throw new Base\exceptions\Exception('Component "' . $name . '" not defined');
    }

    function __call($name, $params)
    {
        if (!empty($this->modules[$name])) {
            return $this->modules[$name];
        }

        throw new Base\exceptions\Exception('Module "' . $name . '" not defined');
    }

    function run()
    {
        $Context = new Context($this->Router, $this->Layout);
        try {
            $Context->dispatch();
        } finally {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo 'ajax';
            } else {
                $Context->Response = $Context->Response->withHeader('Last-Modified', gmdate("D, d M Y H:i:s \G\M\T", LASTMOD));
                $this->emit($Context->Response);
            }
        }
    }

    function end($Context)
    {
        $this->emit($Context->Response);
    }

    public function emit(\Psr\Http\Message\ResponseInterface $response, $maxBufferLevel = null)
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to emit response; headers already sent');
        }

        $reasonPhrase = $response->getReasonPhrase();
        header(sprintf(
                        'HTTP/%s %d%s', $response->getProtocolVersion(), $response->getStatusCode(), ($reasonPhrase ? ' ' . $reasonPhrase : '')
        ));

        foreach ($response->getHeaders() as $header => $values) {
            $name = $this->filterHeader($header);
            $first = true;
            foreach ($values as $value) {
                header(sprintf(
                                '%s: %s', $name, $value
                        ), $first);
                $first = false;
            }
        }

        if (null === $maxBufferLevel) {
            $maxBufferLevel = ob_get_level();
        }
        while (ob_get_level() > $maxBufferLevel) {
            ob_end_flush();
        }
        echo $response->getBody();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    private function filterHeader($header)
    {
        $filtered = str_replace('-', ' ', $header);
        $filtered = ucwords($filtered);
        return str_replace(' ', '-', $filtered);
    }

}
