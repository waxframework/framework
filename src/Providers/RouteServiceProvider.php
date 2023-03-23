<?php

namespace WaxFramework\Providers;

use WaxFramework\Contracts\Provider;
use WaxFramework\App;

class RouteServiceProvider extends \WaxFramework\Routing\Providers\RouteServiceProvider implements Provider
{
    protected static function init_routes( string $type ) {
        parent::$container = App::$container;

        $config = App::$config->get('app');

        parent::$properties = [
            'rest' => $config['rest_api'],
            'ajax' => $config['ajax_api'],
            'middleware' => $config['middleware'],
            'routes-dir' => App::get_dir( "routes" )
        ];
        parent::init_routes($type);
    }
}