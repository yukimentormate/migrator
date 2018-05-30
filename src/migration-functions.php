<?php
require( __DIR__ . '/Classes/Мigrator.php' );

// Input Data
$migrator = new Мigrator;

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
$migrator->validator->is_required( $database );
$migrator->validator->is_required( $new_url );

// check for uploaded file type returns errors.
$allowed_types = array( 'sql', 'zip' );
$file_name = $database[ 'name' ];
$file_extension = pathinfo( $file_name, PATHINFO_EXTENSION );

$migrator->validator->check_file_type( $file_name, $allowed_types );

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
$new_database_string = $migrator->replace_new_prefix( $new_prefix, $new_database_string );

/*
* Creating the new file
*/
$secret_key = md5( microtime() . rand() );
$file_name = 'dump-' . date( 'm-d-h-i-s' ) . '-' . $secret_key . '.sql';
$migrator->file_manipulator->create_new_file( $file_name, $new_database_string, $enable_gzip );

$response[ 'status' ] = 'success';
$response[ 'zip' ] = $enable_gzip;
$response[ 'file_name' ] = $file_name;
echo json_encode( $response );
exit;
