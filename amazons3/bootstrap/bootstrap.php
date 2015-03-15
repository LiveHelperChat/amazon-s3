<?php 

class erLhcoreClassExtensionAmazons3 {

	public function __construct() {
		
	}
		
	private $images_path = '';
	private $files_path = '';
	private $form_path = '';
	
	private $S3Settings = array();
	private $s3Bucket = '';
	
	public function run() {		
		
		// Register autoload
		include 'extension/amazons3/vendor/autoload.php';
		$settings = include 'extension/amazons3/settings/settings.ini.php';
		
		$dispatcher = erLhcoreClassChatEventDispatcher::getInstance();
		 
		$this->S3Settings = array('key' => $settings['key'], 'secret' => $settings['secret']);
		
		$this->s3Bucket = $settings['lhc_bucket'];
		
		$this->images_path = $settings['images_path'];
		$this->files_path = $settings['files_path'];
		$this->form_path = $settings['form_path'];
		
		erLhcoreClassSystem::instance()->WWWDirImages = $settings['s3_files_host'] . '/' . $this->s3Bucket . '/'  .$this->images_path;
		
		/**
		 * User events
		 * */
		$dispatcher->listen('user.edit.photo_store',array($this,'storeUserPhoto'));	
		$dispatcher->listen('user.edit.photo_resize_150',array($this,'userPhotoResize'));	
		$dispatcher->listen('user.remove_photo',array($this,'userRemovePhoto'));
		
		/**
		 * Files events
		 * */
		$dispatcher->listen('file.uploadfile.file_path',array($this,'filePath'));
		$dispatcher->listen('file.uploadfileadmin.file_path',array($this,'filePath'));
		$dispatcher->listen('file.new.file_path',array($this,'filePath'));
		$dispatcher->listen('file.uploadfile.file_store',array($this,'fileStore'));
		$dispatcher->listen('file.uploadfileadmin.file_store',array($this,'fileStore'));
		$dispatcher->listen('file.file_new_admin.file_store',array($this,'fileStore'));
		$dispatcher->listen('file.remove_file',array($this,'fileRemove'));		
		$dispatcher->listen('file.download',array($this,'fileDownload'));	

		/**
		 * Store screenshot functionality
		 * */
		$dispatcher->listen('file.storescreenshot.screenshot_path',array($this,'screenshotPath'));	
		$dispatcher->listen('file.storescreenshot.store',array($this,'fileStore'));	

		/**
		 * Theme listeners
		 * */
		// Themes listeners
		$dispatcher->listen('theme.edit.logo_image_path',array($this,'themeStoragePath'));
		$dispatcher->listen('theme.edit.need_help_image_path',array($this,'themeStoragePath'));
		$dispatcher->listen('theme.edit.offline_image_path',array($this,'themeStoragePath'));
		$dispatcher->listen('theme.edit.online_image_path',array($this,'themeStoragePath'));	
		$dispatcher->listen('theme.edit.operator_image_path',array($this,'themeStoragePath'));	
		$dispatcher->listen('theme.edit.copyright_image_path',array($this,'themeStoragePath'));	
		$dispatcher->listen('theme.edit.restore_image_path',array($this,'themeStoragePath'));	
		$dispatcher->listen('theme.edit.popup_image_path',array($this,'themeStoragePath'));	
		$dispatcher->listen('theme.edit.close_image_path',array($this,'themeStoragePath'));	
		$dispatcher->listen('theme.edit.minimize_image_path',array($this,'themeStoragePath'));	
				
		$dispatcher->listen('theme.temppath',array($this,'themeStoragePath'));
		
		// Theme storage listeners
		$dispatcher->listen('theme.edit.store_logo_image',array($this,'themeStoreFile'));
		$dispatcher->listen('theme.edit.store_need_help_image',array($this,'themeStoreFile'));
		$dispatcher->listen('theme.edit.store_offline_image',array($this,'themeStoreFile'));
		$dispatcher->listen('theme.edit.store_online_image',array($this,'themeStoreFile'));		
		$dispatcher->listen('theme.edit.store_copyright_image',array($this,'themeStoreFile'));		
		$dispatcher->listen('theme.edit.store_operator_image',array($this,'themeStoreFile'));
		$dispatcher->listen('theme.edit.store_restore_image',array($this,'themeStoreFile'));		
		$dispatcher->listen('theme.edit.store_popup_image',array($this,'themeStoreFile'));		
		$dispatcher->listen('theme.edit.store_close_image',array($this,'themeStoreFile'));		
		$dispatcher->listen('theme.edit.store_minimize_image',array($this,'themeStoreFile'));		
				
		// Themes files removement
		$dispatcher->listen('theme.edit.remove_logo_image',array($this,'themeFileRemove'));
		$dispatcher->listen('theme.edit.remove_need_help_image',array($this,'themeFileRemove'));
		$dispatcher->listen('theme.edit.remove_offline_image',array($this,'themeFileRemove'));
		$dispatcher->listen('theme.edit.remove_online_image',array($this,'themeFileRemove'));		
		$dispatcher->listen('theme.edit.remove_operator_image',array($this,'themeFileRemove'));		
		$dispatcher->listen('theme.edit.remove_copyright_image',array($this,'themeFileRemove'));	
		$dispatcher->listen('theme.edit.remove_restore_image',array($this,'themeFileRemove'));	
		$dispatcher->listen('theme.edit.remove_popup_image',array($this,'themeFileRemove'));	
		$dispatcher->listen('theme.edit.remove_close_image',array($this,'themeFileRemove'));	
		$dispatcher->listen('theme.edit.remove_minimize_image',array($this,'themeFileRemove'));	
        		
		// Download events				
		$dispatcher->listen('theme.download_image.logo_image',array($this,'themeFileDownload'));
		$dispatcher->listen('theme.download_image.need_help_image',array($this,'themeFileDownload'));
		$dispatcher->listen('theme.download_image.offline_image',array($this,'themeFileDownload'));
		$dispatcher->listen('theme.download_image.online_image',array($this,'themeFileDownload'));
		$dispatcher->listen('theme.download_image.operator_image',array($this,'themeFileDownload'));
		$dispatcher->listen('theme.download_image.copyright_image',array($this,'themeFileDownload'));				
		$dispatcher->listen('theme.download_image.restore_image',array($this,'themeFileDownload'));
		$dispatcher->listen('theme.download_image.popup_image',array($this,'themeFileDownload'));
		$dispatcher->listen('theme.download_image.close_image',array($this,'themeFileDownload'));
		$dispatcher->listen('theme.download_image.minimize_image',array($this,'themeFileDownload'));

		// Forms module listener
		$dispatcher->listen('form.fill.file_path',array($this,'formFillPath'));
		$dispatcher->listen('form.fill.store_file',array($this,'formStoreFile'));		
		$dispatcher->listen('form.file.download',array($this,'formFileDownload'));		
		$dispatcher->listen('form.remove_file',array($this,'formFileRemove'));	
	}
	
	public function __get($var) {
	    switch ($var) {
	        case 's3':
	           $this->s3 = Aws\S3\S3Client::factory($this->S3Settings);
	           return $this->s3;
	        break;
	        
	        default:
	            ;
	        break;
	    }
	}
	
	/**
	 * Helper function
	 * */
	function get_mime($file) {
		if (function_exists("finfo_file")) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$mime = finfo_file($finfo, $file);
			finfo_close($finfo);
			return $mime;
		} else if (function_exists("mime_content_type")) {
			return mime_content_type($file);
		} else if (!stristr(ini_get("disable_functions"), "shell_exec")) {		
			$file = escapeshellarg($file);
			$mime = shell_exec("file -bi " . $file);
			return $mime;
		} else {
			return false;
		}
	}

	/**
	 * Downloads theme attribute as binnary, in most cases it's image content
	 * */
	public function themeFileDownload($params)
	{	
	    try {	        
	        $imageData = $this->getAmazonS3File($params['theme']->$params['attr']);
	        return array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW, 'filedata' => $imageData);
	    } catch (Exception $e) {
	        return false;
	    }
	    	
		return false;
	}
	
	/**
	 * Stores theme image file in Amazon S3
	 * 
	 * */
	public function themeStoreFile($params)
	{
		$theme = $params['theme'];
	
		if (file_exists($params['file_path']) ) {
						
			$prefixPath = substr(md5($params['file_path']), 0, 4) . '-theme/';
			
			$this->storeToAmazon( $prefixPath . $params['name'], $params['file_path'], $this->get_mime($params['file_path']));
						
			unlink($params['file_path']); // We do not need anymore original file
	
			$theme->$params['path_attr'] = '';
			
			$theme->$params['name_attr'] = $prefixPath . $params['name'];
		}
	}
	
	/**
	 * Store original file in temporary folder also
	 * */
	public function themeStoragePath($params) {
		$params['dir'] = 'var/tmpfiles/';
		return array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW);
	}
	
	/**
	 *  Delete theme image file
	 * */
	public function themeFileRemove($params)
	{
	    $this->deleteS3File($params['name']);	
	}
	
	/**
	 * Downloads filled form attribute
	 * */
	public function formFileDownload($params)
	{	    
	    try {
	        $imageData = $this->getAmazonS3File($params['filename'], 'form_path');
	        return array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW, 'filedata' => $imageData);
	    } catch (Exception $e) {
	        return false;
	    }
	    	    	
		return false;
	}

	/**
	 * Removes filled form attribute
	 * */
	public function formFileRemove($params)
	{
	    $this->deleteS3File($params['filename'],'form_path');	
	}
	
	/**
	 * Forms module file storage override
	 * 
	 * */
	public function formStoreFile($params)
	{
		if ( file_exists($params['file_params']['filepath'] . $params['file_params']['filename']) ) {
								    
		    $prefixPath = substr(md5($params['file_params']['filepath']), 0, 4) . '-form-files/';
		    
		    $this->storeToAmazon($prefixPath . $params['file_params']['filename'], $params['file_params']['filepath'] . $params['file_params']['filename'], $this->get_mime($params['file_params']['filepath'] . $params['file_params']['filename']),false,'form_path',false);
		    
			unlink($params['file_params']['filepath'] . $params['file_params']['filename']); // We do not need anymore original file
		
			$params['file_params']['filename'] = $prefixPath . $params['file_params']['filename'];
			
			erLhcoreClassFileUpload::removeRecursiveIfEmpty('var/', str_replace('var/', '', $params['file_params']['filepath']));
			
			$params['file_params']['filepath'] = '';
		}
	}
	
	/**
	 * Store form files in temporary folder
	 * */
	public function formFillPath($params) {
		$params['dir'] = 'var/tmpfiles/';
		return array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW);
	}
	
	/**
	 * Store screenshot at temporary folder
	 * */
	public function screenshotPath($params) {
		$params['path'] = 'var/tmpfiles/';
		return array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW);
	}

	/**
	 * Download chat file
	 * */
	public function fileDownload($params)
	{
	    $file = $params['chat_file'];
	    	    
	    try {
	        $imageData = $this->getAmazonS3File($file->name, 'files_path');
	        return array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW, 'filedata' => $imageData);
	    } catch (Exception $e) {
	        return false;
	    }	    
	}
	
	/**
	 * 
	 * Store chat file
	 * curl -i http://localhost:8098/buckets/fileschat/keys?keys=true
	 * */
	public function fileStore($params)
	{
		$file = $params['chat_file'];
		
		if (file_exists($file->file_path_server)) {						
		    $prefixPath = substr(md5($file->file_path_server), 0, 4) . '-user-files/';
		    		    
		    $this->storeToAmazon($prefixPath . $file->name, $file->file_path_server, $file->type, false, 'files_path', false);
		    
			unlink($file->file_path_server); // We do not need anymore original file
			
			$file->name = $prefixPath . $file->name;
			$file->file_path = '';
			$file->saveThis();
		}		
	}
	
	/**
	 * Delete from bucket on file removement
	 * */
	public function fileRemove($params)
	{
	    $this->deleteS3File($params['chat_file']->name,'files_path');
	}
	
	/**
	 * store all files in tmpfolder
	 * */
	public function filePath($params) {
		$params['path'] = 'var/tmpfiles/';
	}
	
	/**
	 * Stores files from Amazon S3 in local file system
	 * */
	public function storeTempImage($fileName, $dir) {
	     
	    if ($fileName != '') {
	        	        	        
	       $this->s3->getObject(array(
	            'Bucket' => $this->s3Bucket,
	            'Key'    => $this->images_path . '/' . $fileName,
	            'SaveAs' => $dir . basename($fileName)
	        ));
	       
	        return  $dir .basename($fileName);
	    }
	
	    return false;
	}
	
	/**
	 * Returns image body
	 * */
	public function getAmazonS3File($fileName, $path = 'images_path')
	{
	    // Get an object using the getObject operation
	    $result = $this->s3->getObject(array(
	        'Bucket' => $this->s3Bucket,
	        'Key'    => $this->{$path} . '/' . $fileName,
	    ));
	
	    $body = $result->get('Body');
	    $body->rewind();
	
	    return $body->read($result['ContentLength']);
	}
	
	
	/**
	 * Resizes user profile photo and stores in Amazon S3
	 * */
	public function userPhotoResize($params){
		$response = array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW);
				
		$tmpPath = $this->storeTempImage($params['user']->filename, 'var/tmpfiles/');
		
		erLhcoreClassImageConverter::getInstance()->converter->transform( 'photow_150', $tmpPath, $tmpPath );
		
		$this->storeToAmazon($params['user']->filename, $tmpPath, $params['mime_type']);
		
		unlink($tmpPath);
		
		return $response;
	}
	
	/**
	 * Deletes image/file from S3
	 * */
	public function deleteS3File($fileName, $path = 'images_path')
	{
	    $this->s3->deleteObject(array(
	        'Bucket' => $this->s3Bucket,
	        'Key'    => $this->{$path} . '/' . $fileName
	    ));
	}
		
	/**
	 * Removes user photo from Riak
	 * */
	public function userRemovePhoto($params)
	{
	    $this->deleteS3File($params['user']->filename);	
	}	
	
	public function storeToAmazon($fileName, $filePath, $mimeType = 'image/jpeg', $is_binary = false, $path = 'images_path', $acl = 'public-read')
	{
	    $params = array(
	        'Bucket'     => $this->s3Bucket,
	        'Key'        => $this->{$path} . '/' . $fileName,	    
	        'Metadata'   => array(
	            'Content-Type' => $mimeType,
	        )
	    );

	    if ($acl != '') {
	        $params['ACL'] = $acl;
	    }
	    
	    if ($is_binary == false) {
	        $params['SourceFile'] = $filePath;
	    } else {
	        $params['Body'] = $filePath;
	    }
	     
	    $this->s3->putObject($params);
	}
		
	/**
	 * Stores user photo
	 * */
	public function storeUserPhoto($params) {
		$response = array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW);
		
		$file = qqFileUploader::upload($_FILES,'UserPhoto','var/tmpfiles/');
		if ( !empty($file["errors"]) ) {
			$response['errors'] = $file["errors"];
			return $response;
		}
		
		$prefixPath = substr(md5($params['dir']), 0, 4) . '-profile/';
		
		$this->storeToAmazon($prefixPath . $file['data']['filename'], $file['data']['dir'] . $file['data']['filename'], $file['data']['mime_type']);			
		$file['data']['dir'] = '';
						
		// delete file
		unlink($file['data']['dir'] . $file['data']['filename']);
		
		$file['data']['filename'] = $prefixPath . $file['data']['filename'];
		
		return array('status' => erLhcoreClassChatEventDispatcher::STOP_WORKFLOW, 'data' => $file);
	}
}