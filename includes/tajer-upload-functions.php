<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
function tajer_create_protection_files( $force = false ) {
	if ( false === get_transient( 'tajer_check_protection_files' ) || $force ) {

		$upload_path = tajer_get_upload_dir();

		// Make sure the tajer folder is created
		wp_mkdir_p( $upload_path );

		// Top level .htaccess file
		$rules = tajer_htaccess_rules();
		if ( tajer_htaccess_exists() ) {
			$contents = @file_get_contents( $upload_path . '/.htaccess' );
			if ( $contents !== $rules || ! $contents ) {
				// Update the .htaccess rules if they don't match
				@file_put_contents( $upload_path . '/.htaccess', $rules );
			}
		} elseif ( wp_is_writable( $upload_path ) ) {
			// Create the file if it doesn't exist
			@file_put_contents( $upload_path . '/.htaccess', $rules );
		}

		// Top level blank index.php
		if ( ! file_exists( $upload_path . '/index.php' ) && wp_is_writable( $upload_path ) ) {
			@file_put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}

		// Now place index.php files in all sub folders
		$folders = tajer_scan_folders( $upload_path );
		foreach ( $folders as $folder ) {
			// Create index.php, if it doesn't exist
			if ( ! file_exists( $folder . 'index.php' ) && wp_is_writable( $folder ) ) {
				@file_put_contents( $folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}
		}
		// Check for the files once per day
		set_transient( 'tajer_check_protection_files', true, 3600 * 24 );
	}
}

add_action( 'admin_init', 'tajer_create_protection_files' );

function tajer_htaccess_exists() {
	$upload_path = tajer_get_upload_dir();

	return file_exists( $upload_path . '/.htaccess' );
}

function tajer_scan_folders( $path = '', $return = array() ) {
	$path  = $path == '' ? dirname( __FILE__ ) : $path;
	$lists = @scandir( $path );

	if ( ! empty( $lists ) ) {
		foreach ( $lists as $f ) {
			if ( is_dir( $path . DIRECTORY_SEPARATOR . $f ) && $f != "." && $f != ".." ) {
				if ( ! in_array( $path . DIRECTORY_SEPARATOR . $f, $return ) ) {
					$return[] = trailingslashit( $path . DIRECTORY_SEPARATOR . $f );
				}

				tajer_scan_folders( $path . DIRECTORY_SEPARATOR . $f, $return );
			}
		}
	}

	return $return;
}

function tajer_get_upload_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/tajer' );
	$path = $wp_upload_dir['basedir'] . '/tajer';

	return apply_filters( 'tajer_get_upload_dir', $path );
}

function tajer_custom_upload_dir( $path ) {

	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs
		$time           = current_time( 'mysql' );
		$y              = substr( $time, 0, 4 );
		$m              = substr( $time, 5, 2 );
		$path['subdir'] = "/$y/$m";
	}

	$path['subdir'] = '/tajer' . $path['subdir'];
	$path['path']   = $path['basedir'] . $path['subdir'];
	$path['url']    = $path['baseurl'] . $path['subdir'];

	$path = apply_filters( 'tajer_custom_upload_dir', $path );

	return $path;
}

function tajer_fix_file_array( &$files ) {
	$names = array(
		'name'     => 1,
		'type'     => 1,
		'tmp_name' => 1,
		'error'    => 1,
		'size'     => 1
	);
	foreach ( $files as $key => $part ) {
		// only deal with valid keys and multiple files
		$key = (string) $key;
		if ( isset( $names[ $key ] ) && is_array( $part ) ) {
			foreach ( $part as $position => $value ) {
				$files[ $position ][ $key ] = $value;
			}
			// remove old key reference
			unset( $files[ $key ] );
		}
	}
}

function tajer_htaccess_rules() {

	$rules = "Options -Indexes\n";
	$rules .= "deny from all\n";
	$rules .= "<FilesMatch '\.(jpg|png|gif|mp3|ogg)$'>\n";
	$rules .= "Order Allow,Deny\n";
	$rules .= "Allow from all\n";
	$rules .= "</FilesMatch>\n";


	$rules = apply_filters( 'tajer_htaccess_rules', $rules );

	return $rules;
}

function tajer_secure_upload_location() {
	// Upload location modification
	add_filter( 'upload_dir', 'tajer_custom_upload_dir' );
	add_filter( 'wp_handle_upload_prefilter', 'tajer_custom_upload_dir' );
//	add_filter( 'wp_handle_upload', 'tajer_custom_upload_dir' );
}

function tajer_reset_secure_upload_location() {
	// Upload location modification
	remove_filter( 'upload_dir', 'tajer_custom_upload_dir' );
	remove_filter( 'wp_handle_upload_prefilter', 'tajer_custom_upload_dir' );
//	remove_filter( 'wp_handle_upload', 'tajer_custom_upload_dir' );
}