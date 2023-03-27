<?php

namespace WaxFramework\Providers;

use WaxFramework\Contracts\Provider;
use WaxFramework\App;
use WaxFramework\Routing\Providers\RouteServiceProvider as WaxRouteServiceProvider;

class RouteServiceProvider extends WaxRouteServiceProvider implements Provider {
    public function boot() {
        parent::$container = App::$container;

        $config = App::$config->get( 'app' );

        parent::$properties = [
            'rest'       => $config['rest_api'],
            'ajax'       => $config['ajax_api'],
            'middleware' => $config['middleware'],
            'routes-dir' => App::get_dir( "routes" )
        ];

        parent::boot();
    }
}