<?php

namespace WaxFramework\Request\Route;

use WaxFramework\App;
use WaxFramework\Request\Response;
use WP_REST_Request;

class Ajax extends Route
{
    protected static $ajax_routes = [];

    public static bool $route_found = false;

    protected static function register_route( string $method, string $route, $callback, array $middleware = [] ) {
        if ( $method !== $_SERVER['REQUEST_METHOD'] ) {
            return;
        }

        $route      = static::get_final_route( $route );
        $middleware = array_merge( static::$group_middleware, $middleware );
       
        $path  = '/' . trim( $_REQUEST['action'], '/' );
        $match = preg_match( '@^' . $route . '$@i', $path , $matches );

        if ( ! $match ) {
            return;
        }

        static::$route_found = true;

        static::admin_init( $middleware );

        $is_allowed = static::permission_callback( $middleware );

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
        
        $url_params = [];

        foreach ( $matches as $param => $value ) {
            if ( ! is_int( $param ) ) {
                $url_params[ $param ] = $value;
            }
        }

        static::bind_wp_rest_request( $method, $url_params );

        $response = static::get_callback_response( $callback );

        echo wp_json_encode( $response );
        exit;
    }

    protected static function admin_init( array $middleware ) {

        if ( ! in_array( 'admin', $middleware ) ) {
            return;
        }
        
        if ( ! defined( 'WP_ADMIN' ) ) {
            define( 'WP_ADMIN', true );
        }

        /** Load WordPress Administration APIs */
        require_once ABSPATH . 'wp-admin/includes/admin.php';

        send_nosniff_header();
        nocache_headers();

        /** This action is documented in wp-admin/admin.php */
        do_action( 'admin_init' );
    }

    protected static function bind_wp_rest_request( string $method, array $url_params = [] ) {

        $wp_rest_request = new WP_REST_Request( $method, $_REQUEST['action'] );
        $wp_rest_server  = new \WP_REST_Server;

        $wp_rest_request->set_url_params( $url_params );
        $wp_rest_request->set_query_params( wp_unslash( $_GET ) );
        $wp_rest_request->set_body_params( wp_unslash( $_POST ) );
        $wp_rest_request->set_file_params( $_FILES );
        $wp_rest_request->set_headers( $wp_rest_server->get_headers( wp_unslash( $_SERVER ) ) );
        $wp_rest_request->set_body( $wp_rest_server->get_raw_data() );

        App::$container->set( WP_REST_Request::class, $wp_rest_request );
    }
}