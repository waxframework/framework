<?php

namespace WaxFramework\Request;

class Response
{
    public static function send( array $data, int $status_code = 200, array $headers = [] ) {
        static::set_headers( $headers, $status_code );
        return $data;
    }

    public static function set_headers( array $headers, int $status_code = null, bool $default = true ) {
        if ( headers_sent() ) {
            return;
        }

        if ( $status_code ) {
            status_header( $status_code );
        }

        if ( $default ) {
            $default_headers = [
                'Content-Type' => 'application/json',
                'charset'      => get_option( 'blog_charset' )
            ];
            $headers         = array_merge( $default_headers, $headers );
        }

        foreach ( $headers as $key => $value ) {
            header( "{$key}: {$value}" );
        }
    }
}