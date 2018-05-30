<?php
/*
* Force the page to download the file.
*/

if ( isset( $_GET[ 'ajax' ] ) ) {
	require( 'src/migration-functions.php' );
}

require( 'src/Classes/FileManipulator.php' );
$file_manipulator = new FileManipulator;
$file_manipulator->force_download_file();

require( 'views/master.php' );

