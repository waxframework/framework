<?php

namespace WaxFramework\Request\Route;

use WaxFramework\App;
use WaxFramework\Request\Response;
use WP_REST_Request;

class Ajax extends Route
{
    protected static $ajax_routes = [];

    protected static function register_route( string $method, string $route, $callback, array $middleware = [] ) {
        $action                    = static::get_final_route( $route );
        $middleware                = array_merge( static::$group_middleware, $middleware );
        static::$routes[$action][] = [
            'method'     => $method,
            'middleware' => $middleware,
            'callback'   => $callback
        ];
        add_action( "wp_ajax_{$action}", [ self::class, 'action_wp_ajax' ] );
        add_action( "wp_ajax_nopriv_{$action}", [ self::class, 'action_wp_ajax' ] );
    }

    /**
     * Fires authenticated Ajax actions for logged-in users.
     *
     */
    public static function action_wp_ajax() : void {
        $action = $_REQUEST['action'];
        $routes = static::$routes[$action];
        $key    = array_search( $_SERVER['REQUEST_METHOD'], array_column( $routes, 'method' ) );
        
        if ( ! is_int( $key ) ) {
            Response::set_headers( [], 404 );
            echo wp_json_encode(
                [
                    'code'    => 'ajax_no_route', 
                    'message' => 'No route was found matching the URL and request method.'
                ] 
            );
            exit;
        }

        $route = $routes[$key];

        $is_allowed = static::permission_callback( $route['middleware'] );

        if ( ! $is_allowed ) {
            Response::set_headers( [], 401 );
            echo wp_json_encode(
                [
                    'code'    => 'ajax_forbidden', 
                    'message' => 'Sorry, you are not allowed to do that.'
                ] 
            );
            exit;
        }

        static::bind_wp_rest_request( $route['method'] );
        $response = static::get_callback_response( $route['callback'] );

        echo wp_json_encode( $response );
        exit;
    }

    protected static function bind_wp_rest_request( string $method ) {

        $wp_rest_request = new WP_REST_Request( $method, $_REQUEST['action'] );
        $wp_rest_server  = new \WP_REST_Server;

        $wp_rest_request->set_query_params( wp_unslash( $_GET ) );
        $wp_rest_request->set_body_params( wp_unslash( $_POST ) );
        $wp_rest_request->set_file_params( $_FILES );
        $wp_rest_request->set_headers( $wp_rest_server->get_headers( wp_unslash( $_SERVER ) ) );
        $wp_rest_request->set_body( $wp_rest_server->get_raw_data() );

        App::$container->set( WP_REST_Request::class, $wp_rest_request );
    }

    protected static function format_route_regex( string $route ): string {
        return $route;
    }
}