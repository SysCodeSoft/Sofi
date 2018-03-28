<?php

namespace Sofi;

/**
 * @property \Sofi\HTTP\message\Request   $Request
 * @property \Sofi\Router\Route          $Route
 * @property \Sofi\HTTP\message\Response  $Response
 * @property \Sofi\mvc\web\Page  $Page
 */
class Context extends Router\Context
{
    public $Page;
}
