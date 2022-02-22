<?php 

/**
* Stores general images that come from RentPress
*/
class rentPress_Images_ImageStorage
{
	public function storeAndAttach($postID, $photos = [])
	{
		$photos = is_string($photos) ? json_decode($photos) : $photos;
		if ( ! isset($photos) || count($photos) == 0 ) return 'No photos';
		foreach ($photos as $photo) {
			$filename = $photo->Rank . '_' . $postID . '_' . sanitize_title($photo->Title) . '_' . $photo->ID . '.jpeg';
			$uploaddir = wp_upload_dir();
			$uploadfile = $uploaddir['path'] . '/' . $filename;
			
			if ( ! file_exists($uploadfile) ) {
				$tmp = download_url( $photo->Url );
				 
				$file_array = array(
				    'name' => $filename,
				    'tmp_name' => $tmp
				);
				 
				/**
				 * Check for download errors
				 * if there are error unlink the temp file name
				 */
				if ( is_wp_error( $tmp ) ) {
		            die($this->notifications->errorResponse('Problem downloading photo for property : '.$photo->url));
				}

				$attach_id = media_handle_sideload( $file_array, $propertyPostID );
				/**
				 * We don't want to pass something to $id
				 * if there were upload errors.
				 * So this checks for errors
				 */
				if ( is_wp_error( $attach_id ) ) {
		            die($this->notifications->errorResponse('Problem attaching photo to floor plan post: '.$propertyPostID));
				}
				/**
				 * No we can get the url of the sideloaded file
				 * $value now contains the file url in WordPress
				 * $id is the attachment id
				 */
				$value = wp_get_attachment_url( $attach_id );

			} // end if -- checking if file_exists($uploadfile)
		} // end foreach
	}

}