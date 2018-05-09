<?php
/*
* Force the page to download the file.
*/

if ( isset( $_GET[ 'ajax' ] ) ) {
	require( 'migration-functions.php' );
}

require( 'src/FileManipulate.php' );
$file_manipulate = new FileManipulate;
$file_manipulate->force_download_file();

require( 'views/master.php' );
