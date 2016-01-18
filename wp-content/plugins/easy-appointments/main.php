<?php

/**
 * Plugin Name: Easy Appointments
 * Plugin URI: http://nikolaloncar.com/easy-appointments-wordpress-plugin/
 * Description: Simple managment of Appointments
 * Version: 1.8.2
 * Author: Nikola Loncar
 * Author URI: http://nikolaloncar.com
 * Text Domain: easy-appointments
 * Domain Path: /languages
 */

define('EA_SRC_DIR', dirname( __FILE__ ) . '/src/');
define('EA_JS_DIR', dirname( __FILE__ ) . '/js/');

define('EA_PLUGIN_URL', plugins_url(null,__FILE__) . '/');

/**
 * Entery point
 */
class EasyAppointment
{

	function __construct()
	{
		// on register hook
		register_activation_hook( __FILE__, array($this, 'install'));

		register_uninstall_hook( __FILE__, array('EasyAppointment', 'uninstall'));

		register_deactivation_hook(__FILE__, array('EasyAppointment', 'remove_scheduled_event'));

		add_action( 'plugins_loaded', array($this, 'update'));

		add_action('easyapp_hourly_event',  array($this, 'delete_reservations'));

		// admin panel
		if(is_admin())
		{
			require_once EA_SRC_DIR . 'admin.php';
			$admin = new EAAdminPanel();
			require_once EA_SRC_DIR . 'report.php';
		} else {
			require_once EA_SRC_DIR . 'frontend.php';
			$frontend = new EAFrontend();
		}

		// ajax hooks
		require_once EA_SRC_DIR . 'ajax.php';
		new EAAjax;

	}

	/**
	 * Installation of DB
	 */
	public function install()
	{
		require_once EA_SRC_DIR . 'install.php';

		$install = new EAInstallTools();

		if($install->easy_app_db_version != get_option( 'easy_app_db_version')) {
			$install->init_db();
			$install->init_data();
		}

		wp_schedule_event(time(), 'hourly', 'easyapp_hourly_event');
	}

	/**
	 * Remove tables of Appointments plugin
	 */
	public static function uninstall()
	{
		require_once EA_SRC_DIR . 'uninstall.php';

		$uninstall = new EAUninstallTools();

		$uninstall->drop_db();
		$uninstall->delete_db_version();
	}

	/**
	 * Remove cron action
	 */
	public static function remove_scheduled_event()
	{
		wp_clear_scheduled_hook('easyapp_hourly_event');
	}

	public function update()
	{
		// register domain
		$this->register_text_domain();

		// update database
		require_once EA_SRC_DIR . 'dbmodels.php';
		require_once EA_SRC_DIR . 'install.php';
		require_once EA_SRC_DIR . 'metafields.php';

		$tools = new EAInstallTools();
		$tools->update();
	}

	public function register_text_domain()
	{
		load_plugin_textdomain( 'easy-appointments', FALSE, basename(dirname( __FILE__ )) . '/languages/' );
	}

	public function delete_reservations()
	{
		require_once EA_SRC_DIR . 'dbmodels.php';
		EADBModels::delete_reservations();
	}
}

$ea_app = new EasyAppointment;
