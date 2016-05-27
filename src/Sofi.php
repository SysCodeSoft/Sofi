<?php

namespace Sofi\Base;

/**
 * Сокращение для DIRECTORY_SEPARATOR
 */
define('DS', DIRECTORY_SEPARATOR);

class Sofi
{

    /**
     * Базовые настройким <br>
     * устанавливаются методом addConfig(array)
     * @see addConfig()
     * @var array 
     */
    public static $general = [
        'time-zone' => 'Europe/Moscow',
        'time-limit' => 30,
        'charset' => 'UTF-8',
        'ignore_user_abort' => false,
        'ini' => [],
        'autoloader' => false
    ];

    /**
     * Возвращает true если запущенно из консоли
     * 
     * @return boolean
     */
    public static function isConsole()
    {
        return PHP_SAPI == 'cli' ||
                (!isset($_SERVER['DOCUMENT_ROOT']) &&
                !isset($_SERVER['REQUEST_URI']));
    }

    /**
     * Добавление параметров конфигурации
     * 
     * @param array $config
     */
    public static function addConfig($config = [])
    {
        self::$general = array_merge(self::$general, $config);
    }

    /**
     * Иницилизация фреймворка
     * - загрузка конфигураций
     * - установка параметров
     * - установка путей
     * ...
     * 
     * @param array $config
     * @return \Sofi\Base\Sofi
     */
    public static function init(...$config)
    {
        /**
         * Определение пути запуска скрипта
         */
        if (!defined('PUBLIC_PATH')) {
            $debug = debug_backtrace();
            define('PUBLIC_PATH', realpath(dirname($debug[0]['file'])) . DS);
        }

        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(PUBLIC_PATH) . DS);
        }

        self::addConfig(self::getConfig(...$config));

        /*
         * General init
         */
        foreach (self::$general['ini'] as $ini => $val) {
            if (is_int($ini)) {
                ini_set($val, true);
            } else {
                ini_set($ini, $val);
            }
        }

        ignore_user_abort(self::$general['ignore_user_abort']);

        set_time_limit(self::$general['time-limit']);
        date_default_timezone_set(self::$general['time-zone']);
        mb_internal_encoding(self::$general['charset']);
        mb_regex_encoding(self::$general['charset']);

        return self;
    }

    function __construct()
    {
        ;
    }

}
