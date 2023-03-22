<?php

namespace WaxFramework\Providers;

use WaxFramework\Contracts\Provider;
use WaxFramework\App;
use WaxFramework\Request\Route\DataBinder;

class RouteServiceProvider extends Provider
{
    public function boot() {
        add_action( 'rest_api_init', [$this, 'action_rest_api_init'] );
        add_action( 'init', [ $this, 'action_ajax_init' ] );
    }

    /**
     * Fires after WordPress has finished loading but before any headers are sent.
     */
    public function action_ajax_init() : void {
        $this->init_routes( 'ajax' );
    }

    /**
     * Fires when preparing to serve a REST API request.
     */
    public function action_rest_api_init(): void {
        $this->init_routes( 'rest' );
    }

    private function init_routes( string $type ) {
        $data_binder = App::$container->get( DataBinder::class );

        $namespace = App::$config->get( "app.{$type}_api.namespace" );

        $data_binder->set_namespace( $namespace );

        include App::get_dir( "routes/{$type}/api.php" );

        $versions = App::$config->get( "app.{$type}_api.versions" );

        if ( is_array( $versions ) ) {

            foreach ( $versions as $version ) {
                $version_file = App::get_dir( "routes/{$type}/{$version}.php" );

                if ( is_file( $version_file ) ) {
                    $data_binder->set_version( $version );
                    include $version_file;
                }
            }
        }
    }
}