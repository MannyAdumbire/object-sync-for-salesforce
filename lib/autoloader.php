<?php
/**
 * Automatically loads the specified file.
 *
 * @package Object Sync for Salesforce
 */

/**
 * Enable autoloading of plugin classes
 *
 * @param $class_name
 */
spl_autoload_register(
	function ( $class_name ) {

		// Only autoload classes from this plugin.
		if ( $class_name !== 'Object_Sync_Salesforce' && strpos( $class_name, 'Object_Sync_' ) !== 0 ) {
			return;
		}

		// wpcs style filename for each class.
		$file_name = 'class-' . str_replace( '_', '-', strtolower( $class_name ) );

		// create file path.
		$file = dirname( OBJECT_SYNC_SF_FILE ) . '/classes/' . $file_name . '.php';

		// If a file is found, load it.
		if ( file_exists( $file ) ) {
			require_once $file;
		}

	}
);
