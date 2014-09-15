<?php

class CoreFilesUpload extends CoreBase {
	public static $_instance = null;

	public static function getInstance() {
		if (null === self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function getMimeType($ext) {
		$ext = strtolower($ext);
		switch($ext) {
			case 'jpg':
			case 'jpeg':
				return array("image/jpeg", "image/jpg", "image/pjpeg");
			case 'png':
				return array("image/png", "image/x-png");
			case 'gif':
				return array("image/gif");
		}
		return array();
	}

	public function upload($params) {
		$upload_path = $params['upload_path'];
		$max_filesize_mb = $params['max_filesize_mb'];
		$allowed_exts = $params['allowed_exts'];
		$allowed_exts_arr = explode('|', $allowed_exts);

		if(empty($_FILES[$params['file_key']])) {
			$this->addError('No files selected for upload, please try again or contact support');
			return false;
		}
		$file = $_FILES[$params['file_key']];

		if($file['error'] != UPLOAD_ERR_OK) {
			if($file['error'] == UPLOAD_ERR_INI_SIZE || $file['error'] == UPLOAD_ERR_FORM_SIZE) {
				// UPLOAD_ERR_INI_SIZE - The uploaded file exceeds the upload_max_filesize directive in php.ini
				// UPLOAD_ERR_FORM_SIZE - The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form
				$this->addError('Uploaded image too large, image size must not exceed '.$max_filesize_mb.' MB');
			}else {
				$this->addError('Error occured while uploading image, please try again or contact support');
			}
			return false;
		}
		$file_name = $file['name'];
		$file_size = $file["size"];

		if(!preg_match("/\." . $allowed_exts . "$/i", $file_name)) {
			$this->addError('Image file have wrong extension, allowed extensions are '. implode(', ', $allowed_exts_arr));
			return false;
		}

		if($file_size > $max_filesize_mb * 1048576) {
			$this->addError('Uploaded image too large, image size must not exceed '.$max_filesize_mb.' MB');
			return false;
		}

		if($file_size == 0) {
			$this->addError('File is empty, please upload valid image');
			return false;
		}

		$pathinfo = pathinfo($file_name);
		$ext = preg_replace('/[^a-z]/i', '', $pathinfo['extension']);

		$allowed_mime_types = $this->getMimeType($ext);
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
  	$mime_type = finfo_file($finfo, $file['tmp_name']);
  	finfo_close($finfo);
		if (!in_array($mime_type, $allowed_mime_types)) {
			$this->addError('Image file have wrong format, allowed extensions are '. implode(', ', $allowed_exts_arr));
			return false;
		}

		// Random file name
//		$file_name_to_save = md5(time().rand()).'.'.$ext;
//		$file_path_to_save = $upload_path.$file_name_to_save;
//		while (file_exists($file_path_to_save)) {
//			$file_name_to_save = md5(time().rand()).'.'.$ext;
//			$file_path_to_save = $upload_path.$file_name_to_save;
//		}

		$file_name = str_replace(' ', '_', $file_name);
		$file_name = str_replace('&', '_and_', $file_name);
		$file_name = preg_replace('/[^a-z0-9._-]/i', '', $file_name);
		$filename_w_o_ext = basename($file_name, ".".$ext);

		$file_name_to_save = $filename_w_o_ext.($ext?'.'.$ext:'');
		$file_path_to_save = $upload_path.$file_name_to_save;
		$i = 0;
		while (file_exists($file_path_to_save)) {
			$i++;
			$file_name_to_save = $filename_w_o_ext.$i.($ext?'.'.$ext:'');
			$file_path_to_save = $upload_path.$file_name_to_save;
		}


		if (!move_uploaded_file($file['tmp_name'], $file_path_to_save)) {
			$this->addError('Error occured while uploading image, please try again or contact support');
			return false;
		}

		return $file_name_to_save;
	}
}