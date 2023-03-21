<?php

namespace WaxFramework\Route;

class Route
{
    protected static string $route_prefix = '';

    protected static array $routes = [];

    protected static array $group_middleware = [];

    public static function group( string $prefix, \Closure $callback, array $middleware = [] ) {
        $previous_route_prefix     = static::$route_prefix;
        $previous_route_middleware = static::$group_middleware;

        static::$route_prefix    .= $prefix;
        static::$group_middleware = array_merge( static::$group_middleware, $middleware );

        call_user_func( $callback );

        static::$route_prefix     = $previous_route_prefix;
        static::$group_middleware = $previous_route_middleware;
    }

    public static function get( string $route, $callback, array $middleware = [] ) {
        static::register_route( 'GET', $route, $callback, $middleware );
    }

    public static function post( string $route, $callback, array $middleware = [] ) {
        static::register_route( 'POST', $route, $callback, $middleware );
    }

    public static function put( string $route, $callback, array $middleware = [] ) {
        static::register_route( 'PUT', $route, $callback, $middleware );
    }

    public static function patch( string $route, $callback, array $middleware = [] ) {
        static::register_route( 'PATCH', $route, $callback, $middleware );
    }

    public static function delete( string $route, $callback, array $middleware = [] ) {
        static::register_route( 'DELETE', $route, $callback, $middleware );
    }

    public static function resources( array $resources, array $middleware = [] ) {
        foreach ( $resources as $resource => $callback ) {
            static::resource( $resource, $callback, [], $middleware );
        }
    }

    public static function resource( string $route, $callback, array $take = [], array $middleware = [] ) {
        $routes = [
            'index'  => [
                'method' => 'GET',
                'route'  => $route
            ],
            'store'  => [
                'method' => 'POST',
                'route'  => $route
            ],
            'show'   => [
                'method' => 'GET',
                'route'  => $route . '/{id}'
            ],
            'update' => [
                'method' => 'PATCH',
                'route'  => $route . '/{id}'
            ],
            'delete' => [
                'method' => 'DELETE',
                'route'  => $route . '/{id}'
            ],
        ];

        if ( ! empty( $take ) ) {
            if ( isset( $take['type'] ) && 'only' === $take['type'] ) {
                $routes = array_intersect_key( $routes, array_flip( $take['items'] ) );
            } else {
                $routes = array_diff_key( $routes, array_flip( $take['items'] ) );
            }
        }

        foreach ( $routes as $callback_method => $args ) {
            static::register_route( $args['method'], $args['route'], [$callback, $callback_method], $middleware );
        }
    }

    protected static function register_route( string $method, string $route, $callback, array $middleware = [] ) {
        $route      = static::$route_prefix . $route;
        $middleware = array_merge( static::$group_middleware, $middleware );

        static::$routes[] = [
            'method'     => $method,
            'route'      => $route,
            'callback'   => $callback,
            'middleware' => $middleware,
        ];
    }
}