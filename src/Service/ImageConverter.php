<?php

// src/Service/ImageConverter.php
namespace App\Service;

use Imagick;

class ImageConverter {

	/**
  	 * Compile number of images into one PDF File
  	 */
	public function images_to_pdf($images, $pdf_name)
	{
		$pdf = new Imagick($images);
		$pdf->setImageFormat('pdf');
		$pdf->writeImages($pdf_name, true);

		return $pdf_name;

	}

	/**
	 * Convert Base64 Image into jpeg
	 * @return $output_file String
	 */
	public function base64_to_jpeg($base64_string, $output_file) {
    	// open the output file for writing
	    $ifp = fopen( $output_file, 'wb' ); 

	    // split the string on commas
	    // $data[ 0 ] == "data:image/png;base64"
	    // $data[ 1 ] == <actual base64 string>
	    $data = explode( ',', $base64_string );

	    // we could add validation here with ensuring count( $data ) > 1
	    fwrite( $ifp, base64_decode( $data[ 1 ] ) );

	    // clean up the file resource
	    fclose( $ifp ); 

	    return $output_file; 
	}

}