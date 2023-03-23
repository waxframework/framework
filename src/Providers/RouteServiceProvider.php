<?php

namespace WaxFramework\Providers;

use WaxFramework\Contracts\Provider;
use WaxFramework\App;
use WaxFramework\Request\Response;
use WaxFramework\Request\Route\DataBinder;
use WaxFramework\Request\Route\Ajax;

class RouteServiceProvider extends Provider
{
    public function boot() {
        add_action( 'rest_api_init', [$this, 'action_rest_api_init'] );
    }

    /**
     * Fires when preparing to serve a REST API request.
     */
    public function action_rest_api_init(): void {
        $this->init_routes( 'rest' );
    }

    /**
     * Fires after WordPress has finished loading but before any headers are sent.
     */
    public function ajax_init() : void {
        $this->init_routes( 'ajax' );
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

        if ( 'ajax' === $type && ! Ajax::$route_found ) {
            Response::set_headers( [], 404 );
            echo wp_json_encode(
                [
                    'code'    => 'ajax_no_route', 
                    'message' => 'No route was found matching the URL and request method.'
                ] 
            );
            exit;
        }
    }
}