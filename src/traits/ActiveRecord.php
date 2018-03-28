<?php

namespace Sofi\traits;

trait ActiveRecord
{

    static public function db()
    {
        return \Sofi\Base\Sofi::app()->DB->default;
    }


}
