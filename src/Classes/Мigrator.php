<?php

require( __DIR__ . '\Validator.php' );
require( __DIR__ . '\FileManipulator.php' );

class Мigrator {
    public $validator;
    public $file_manipulator;

    public function __construct() {
        $validator = new Validator;
        $file_manipulator = new FileManipulator;

        $this->validator = $validator;
        $this->file_manipulator = $file_manipulator;
    }

    /**
     * checks the extension and returns the database string
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
            $validate = new Validate;
            $database_string = $validate->unzip_database( $database_file, $unzip_needed );
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
    public function replace_new_prefix( $new_prefix, $database_string ){
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

            $database_string = str_replace( $old_prefix, $new_prefix, $database_string );
        }

        return $database_string;
    }
}
