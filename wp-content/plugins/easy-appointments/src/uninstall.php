<?php

/**
* Install tools
*
* Create whole DB stracture
*/
class EAUninstallTools
{
	public function drop_db()
	{
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ea_fields" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ea_appointments" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ea_connections" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ea_locations" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ea_services" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ea_staff" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ea_options" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ea_meta_fields" );
	}

	public function delete_db_version()
	{
		$option_name = 'easy_app_db_version';

		delete_option( $option_name );
	}
}