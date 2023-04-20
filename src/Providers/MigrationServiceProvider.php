<?php

namespace WaxFramework\Providers;

use WaxFramework\App;
use WaxFramework\Contracts\Migration;
use WaxFramework\Contracts\Provider;

class MigrationServiceProvider implements Provider {

    public function boot() {
        $migrations      = App::$config->get( 'app.migrations' );
        $current_version = App::$config->get( 'app.version' );
        $option_key      = App::$config->get( 'app.migration_db_option_key' );

        $executed_migrations = get_option( $option_key, [] );

        foreach ( $migrations as $key => $migration_class ) {

            if ( in_array( $key, $executed_migrations ) ) {
                continue;
            }

            $migration = App::$container->get( $migration_class );

            if ( ! $migration instanceof Migration ) {
                continue;
            }

            if ( 1 !== version_compare( $current_version, $migration->more_than_version() ) ) {
                continue;
            }

            if ( $migration->execute() ) {
                $executed_migrations[] = $key;
                update_option( $option_key, $executed_migrations );
            }
            break;
        }
    }
}