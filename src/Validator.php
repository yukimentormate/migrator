<?php
class Validator {

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
 }
