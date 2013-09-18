<?php
/*
Plugin Name: WP MySQLi
Description: Enables MySQLi
Author: Marko Heijnen
Author URI: http://markoheijnen.com
Text Domain: mysqli
Version: 1.1
*/

if ( !defined( 'ABSPATH' ) ) {
	die();
}

class MySQLi_Manager {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
	}

	/**
	 * Try to delete the custom db.php drop-in.  This doesn't use the
	 * WP_Filesystem, because it's not available.
	 */
	public static function deactivate() {
		if( file_exists( WP_CONTENT_DIR . '/db.php' ) ) {
			$crc1 = md5_file( dirname( __FILE__ ) . '/db.php' );
			$crc2 = md5_file( WP_CONTENT_DIR . '/db.php' );

			if ( $crc1 === $crc2 ) {
				if ( false === @unlink( WP_CONTENT_DIR . '/db.php' ) ) {
					wp_die( __( 'Please remove the custom db.php drop-in before deactivating MySQLi', 'mysqli' ) );
				}
			}
		}
	}

	/**
	 * Uninstall
	 */
	public static function uninstall() {
		global $wp_filesystem;

		if( file_exists( WP_CONTENT_DIR . '/db.php' ) ) {
			$crc1 = md5_file( dirname( __FILE__ ) . '/db.php' );
			$crc2 = md5_file( WP_CONTENT_DIR . '/db.php' );

			if ( $crc1 === $crc2 ) {
				$wp_filesystem->delete( $wp_filesystem->wp_content_dir() . '/db.php' );
			}
		}
	}

	public function add_page() {
		add_management_page(
			'MySQLi',
			'MySQLi',
			'manage_options',
			'mysqli',
			array( $this, 'page_overview' )
		);
	}

	public function page_overview() {
		if ( ! current_user_can( 'manage_options' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

		echo '<div class="wrap">';

		screen_icon('options-general');
		echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';

		// Don't force a specific file system method
		$method = '';

		// Define any extra pass-thru fields (none)
		$form_fields = array();

		// Define the URL to post back to (this one)
		$url = $_SERVER['REQUEST_URI'];

		// Install flags
		$do_install = ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'install-db-nonce' ) );
		$do_uninstall = ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'uninstall-db-nonce' ) );

		if ( $do_install || $do_uninstall ) {

			// Ask for credentials, if necessary
			if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $form_fields ) ) ) {

				return true;
			} elseif ( ! WP_Filesystem($creds) ) {

				// The credentials are bad, ask again
				request_filesystem_credentials( $url, $method, true, false, $form_fields );
				return true;
			} else {
				// Once we get here, we should have credentials, do the file system operations
				global $wp_filesystem;

				// Install
				if ( $do_install ) {
					if ( $wp_filesystem->put_contents( $wp_filesystem->wp_content_dir() . '/db.php' , file_get_contents( dirname( __FILE__ ) .'/db.php' ), FS_CHMOD_FILE ) ) {
						echo '<div class="updated"><p><strong>' . __( 'db.php has been installed.', 'mysqli' ) .'</strong></p></div>';
					} else {
						echo '<div class="error"><p><strong>' . __( "db.php couldn't be installed. Please try is manually", 'mysqli' ) .'</strong></p></div>';
					}

				// Remove
				} elseif ( $do_uninstall ) {
					if ( $wp_filesystem->delete( $wp_filesystem->wp_content_dir() . '/db.php' ) ) {
						echo '<div class="updated"><p><strong>' . __( 'db.php has been removed.', 'mysqli' ) .'</strong></p></div>';
					} else {
						echo '<div class="error"><p><strong>' . __( "db.php couldn't be removed. Please try is manually", 'mysqli' ) .'</strong></p></div>';
					}

				}
			}
		}

		echo '<div class="tool-box"><h3 class="title">' . __( 'Current driver', 'mysqli' ) . '</h3></div>';
		echo '<p>' . __( "The button below let's you install/remove the MySQLi drive" ) . '</p>';

		if( file_exists( WP_CONTENT_DIR . '/db.php' ) ) {
			$crc1 = md5_file( dirname( __FILE__ ) . '/db.php' );
			$crc2 = md5_file( WP_CONTENT_DIR . '/db.php' );

			if ( $crc1 === $crc2 ) {
				echo '<form method="post" style="display: inline;">';
				wp_nonce_field('uninstall-db-nonce');

				echo '<p>';

				if( function_exists( 'mysql' ) )
					submit_button( __( 'Remove', 'mysqli' ), 'primary', 'install-db-php', false );

				echo '</p>';

				echo '</form>';

			} else {
				echo '<form method="post" style="display: inline;">';
				wp_nonce_field('install-db-nonce');

				echo '<p><strong>' . __( 'Another db.php is installed', 'mysqli' ) . '</strong> &nbsp; ';
				submit_button( __( 'Install our driver', 'mysqli' ), 'primary', 'install-db-php', false );
				echo '</p>';

				echo '</form>';
			}
		}
		else {
			echo '<form method="post" style="display: inline;">';
			wp_nonce_field('install-db-nonce');

			echo '<p><strong>' . __( 'No custom db.php installed', 'mysqli' ) . '</strong> &nbsp; ';
			submit_button( __( 'Install', 'mysqli' ), 'primary', 'install-db-php', false );
			echo '</p>';

			echo '</form>';
		}

	}

}

if( is_admin() )
	new MySQLi_Manager;

register_deactivation_hook( __FILE__, array( 'MySQLi_Manager', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'MySQLi_Manager', 'uninstall' ) );
