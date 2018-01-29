<?php

// Input Data
$migrator = new Migrator;
$response = array(
	'status' => 'error',
	'message' => ''
);

isset( $_FILES[ 'database-file' ] ) ? $database = $_FILES[ 'database-file' ] : $database = false;
isset( $_POST[ 'new-url' ] ) ? $new_url = htmlspecialchars( $_POST[ 'new-url' ] ) : $new_url = false;
isset( $_POST[ 'new-prefix' ] ) ? $new_prefix = htmlspecialchars( $_POST[ 'new-prefix' ] ) : $new_prefix = false;
isset( $_POST[ 'enable-gzip' ] ) ? $enable_gzip = $_POST[ 'enable-gzip' ] : $enable_gzip = false;
$unzip_needed = true;

// check for empty input and return errors if needed.
$migrator->is_required( $database );
$migrator->is_required( $new_url );

// check for uploaded file type returns errors.
$allowed_types = array( 'sql', 'zip' );
$file_name = $database[ 'name' ];
$file_extension = pathinfo( $file_name, PATHINFO_EXTENSION );

$migrator->check_file_type( $file_name, $allowed_types );

/* check the extension and returns the database string
*  if it is zip file - uncomporess the archive and return the database string;
*  if it is sql - return the database string;
*/
$database_string = $migrator->get_database_string( $file_extension, $database, $unzip_needed );

/* regex function to find the current site url
*  requires the database string;
*/
$site_url = $migrator->get_current_site_url( $database_string );

/*
* new database string with the site url replaced, the serialize data is also replaced.
*/
$new_database_string = $migrator->recursive_unserialize_replace( $site_url, $new_url, $database_string );

/*
* new database string with the new prefix replaced
*/
$new_database_string = $migrator->check_and_replace_new_prefix( $new_prefix, $new_database_string );

/*
* Creating the new file
*/
$secret_key = md5( microtime() . rand() );
$file_name = 'dump-' . date( 'm-d-h-i-s' ) . '-' . $secret_key . '.sql';
$migrator->create_new_file( $file_name, $new_database_string, $enable_gzip );

$response[ 'status' ] = 'success';
$response[ 'zip' ] = $enable_gzip;
$response[ 'file_name' ] = $file_name;
echo json_encode( $response );
exit;

/**
 * The class starts here if needed can be moved to file.
*/
class Migrator {

	public function __construct() {
		//
	}

	/**
	 * check for empty input and return errors if needed.
	 * @param $field - the field to check for;
	 *
	 * @return Error if the $field is empty.
	*/
	public function is_required( $field ){
		if ( empty( $field ) ) {
			$response[ 'status' ] = 'error';
			$response[ 'message' ] = 'This Field is Required!';
			echo json_encode( $response );
			exit;
		}
	}

	/**
	 * check for uploaded file type returns errors.
	 * @param $file_name - the file name;
	 * @param $allowed_types - the allowed extensions;
	 *
	 * @return Error if the allowed type is not match.
	*/
	public function check_file_type( $file_name, $allowed_types = array() ) {
		$file_extension = pathinfo( $file_name, PATHINFO_EXTENSION );

		if( !in_array( $file_extension, $allowed_types ) ) {
			$response[ 'status' ] = 'error';
			$response[ 'message' ] = 'The type is not allowed. Please upload .sql or zip formats.';
			echo json_encode( $response );
			exit;
		}
	}

	/**
	 * check the extension and returns the database string
	 * @param file_extension - check the file's extension;
	 * @param database_file - databse file from the input;
	 * @param unzip_needed - does the uploaded file need unzipping;
	 *
	 * @return The database string.
	*/
	public function get_database_string( $file_extension, $database_file, $unzip_needed ){
		if ( $file_extension === 'sql' ) {
			$unzip_needed = false;

			if ( $database_file[ 'error' ] == UPLOAD_ERR_OK && is_uploaded_file( $database_file[ 'tmp_name' ] ) ) {
				$database_string = file_get_contents( $database_file[ 'tmp_name' ] );
			}
		} else {
			if ( $unzip_needed ) {
				$path = pathinfo( realpath( $database_file[ 'tmp_name' ] ), PATHINFO_DIRNAME );
				ob_clean();

				$zip = new ZipArchive;
				$res = $zip->open( $database_file[ 'tmp_name' ] );

				if ( $res === TRUE ) {
					for ( $i = 0; $i < $zip->numFiles; $i++ ) {
						$zip_file_name = $zip->getNameIndex( $i );
					}

					$zip->extractTo( $path );
					$zip->close();

					$database_string = file_get_contents( $path . '/' . $zip_file_name );
				}
			}
		}

		return $database_string;
	}

	/**
	 * Regex public function to find the current site url
	 * @param database_string -  the database string;
	 *
	 * @return The site url of the database provided.
	*/
	public function get_current_site_url( $database_string ){
		$site_url_check = preg_match( "/\'siteurl\'\,\s\'(..*?)\'/", $database_string, $matches );

		if( !empty( $site_url_check ) && is_array( $matches ) ) {
			$site_url = $matches[ 1 ];
		}

		if( empty( $site_url ) ) {
			$response[ 'status' ] = 'error';
			$response[ 'message' ] = 'The database file seems to be corrupted, please check the file and try again!';
			echo json_encode( $response );
			exit;
		}

		return $site_url;
	}

	/**
	 * Take a serialised array and unserialise it replacing elements as needed and
	 * unserialising any subordinate arrays and performing the replace on those too.
	 *
	 * @param $search       String we're looking to replace.
	 * @param $replace      What we want it to be replaced with
	 * @param $data         Used to pass any subordinate arrays back to in.
	 * @param $serialised   Does the array passed via $data need serialising.
	 *
	 * @return The original array with all elements replaced as needed.
	 */
	public function recursive_unserialize_replace( $search = '', $replace = '', $data = '', $serialised = false ) {
		$replace = $this->match_url_last_chars( $search, $replace );

		try {

			if ( is_string( $data ) && ( $unserialized = @unserialize( $data ) ) !== false ) {
				$data = $this->recursive_unserialize_replace( $search, $replace, $unserialized, true );

			} elseif ( is_array( $data ) ) {
				$_tmp = array( );

				foreach ( $data as $key => $value ) {
					$_tmp[ $key ] = $this->recursive_unserialize_replace( $search, $replace, $value, false );
				}

				$data = $_tmp;
				unset( $_tmp );

			} elseif ( is_object( $data ) ) {
				$_tmp = $data;
				$props = get_object_vars( $data );

				foreach ( $props as $key => $value ) {
					$_tmp->$key = $this->recursive_unserialize_replace( $search, $replace, $value, false );
				}

				$data = $_tmp;
				unset( $_tmp );

			} else {
				if ( is_string( $data ) ) {
					$data = str_replace( $search, $replace, $data );
				}
			}

			if ( $serialised ){
				return serialize( $data );
			}

		} catch( Exception $error ) {
		}

		return $data;
	}

	/**
	 * Functions to trim or add '/' to the new url
	 * @param old_url - old site's url;
	 * @param new_url - new site's url;
	 *
	 * @return The new site url with or without '/'.
	*/
	public function match_url_last_chars( $old_url, $new_url ) {
		$old_url_last_char = substr( $old_url, -1 );
		$new_url_last_char = substr( $new_url, -1 );

		if ( $old_url_last_char === '/' && $new_url_last_char !== '/' ) {
			$new_url .= '/';
		} else if ( $old_url_last_char !== '/' && $new_url_last_char == '/' ) {
			$new_url = rtrim( $new_url, '/' );
		}

		return $new_url;
	}

	/**
	 * function that returns the new database string with the new prefix replaced
	 * @param new_prefix - the new prefix that will replace the old one;
	 * @param database_string - the string to replace;
	 *
	 * @return The original string with all elements replaced as needed.
	*/
	public function check_and_replace_new_prefix( $new_prefix, $database_string ){
		$new_database_string = $database_string;

		if( !empty( $new_prefix ) ) {
			$new_prefix_last_char = substr( $new_prefix, -1 );

			if ( $new_prefix_last_char !== '_' ) {
				$new_prefix .= '_';
			}

			$new_prefix_check = preg_match( "/(?<= `)(.*)(?=users)/", $database_string, $prefix_matches );

			if( !empty( $new_prefix_check ) && is_array( $prefix_matches ) ) {
				$old_prefix = $prefix_matches[ 1 ];
			} else {
				$response[ 'status' ] = 'error';
				$response[ 'message' ] = 'The Users tables did not exists, please check the sql file provided!';
				exit;
			}

			$new_database_string = str_replace( $old_prefix, $new_prefix, $database_string );
		}

		return $new_database_string;
	}

	/**
	 * Compressed the file provided to gz format
	 * @param $source - the file to be gziped;
	 * @param $level - level / scale of zipping;
	 *
	 * @return The path to the zip file.
	*/
	public function gz_compress_file( $source, $level = 9 ){
		$dest = $source . '.gz';
		$mode = 'wb' . $level;
		$error = false;

		if ( $fp_out = gzopen( $dest, $mode ) ) {
			if ( $fp_in = fopen( $source,'rb' ) ) {
				while (!feof( $fp_in ) )
					gzwrite( $fp_out, fread( $fp_in, 1024 * 512 ) );
				fclose( $fp_in );
			} else {
				$error = true;
			}
			gzclose( $fp_out );
		} else {
			$error = true;
		}

		if ( $error ) {
			return false;
		} else {
			return $dest;
		}
	}

	/**
	 *  Creating the new file
	 * @param file_name - the file name that will be saved.;
	 * @param file_string - the string to put in the file;
	 * @param enable_gzip ( bool ) - flag to enable / disbale the zipping;
	*/
	public function create_new_file( $file_name, $file_string, $enable_gzip ){
		set_time_limit( 0 );

		$file_handle = fopen( $file_name, 'w' );

		fwrite( $file_handle, $file_string );
		fclose( $file_handle );

		if( !empty( $enable_gzip ) ) {
			$this->gz_compress_file( $file_name );
			unlink( $file_name );
		}
	}
}
