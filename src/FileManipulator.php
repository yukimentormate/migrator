<?php
class FileManipulator {

    /**
     *  Unzip (if needed) the file provided.
     * @param database_file - the needed file .;
     * @param unzip_needed - if unzipping is necessary;
     *
     * @return The string of the file provided.
    */
    public function unzip_database( $database_file, $unzip_needed ){
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

        return $database_string;
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
     * @param enable_gzip ( bool ) - flag to enable / disable the zipping;
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

    public function force_download_file(){
        $file_name = '';

        if ( isset( $_GET[ 'fn' ] ) ) {
            $file_name = $_GET[ 'fn' ];
        }

        if ( isset( $_GET[ 'zip' ] ) && ( $_GET[ 'zip' ] !== 'false' ) ) {
            $file_name = $file_name . '.gz';
        }

        if ( file_exists( $file_name ) ) {
            header( 'Content-Type: application/octet-stream');
            header( "Content-Transfer-Encoding: Binary");
            header( "Content-disposition: attachment; filename=\"" . basename( $file_name ) . "\"" );
            readfile( $file_name );
            ignore_user_abort( true );
            unlink( $file_name );
            exit;
        }
    }
}
