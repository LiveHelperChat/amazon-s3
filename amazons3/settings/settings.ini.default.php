<?php 

return array (
    
    // LHC has three different file types
    // lhc-images are always public
    // lhc-files and lhc-form-files are protected
	'images_path' => 'lhc-images',
	'files_path' => 'lhc-files',
	'form_path' => 'lhc-form-files',
    
    // Bucket name
    'lhc_bucket' => 'lhc-cloud',
    
    // Authentification
    's3_files_host' => 'https://s3-eu-west-1.amazonaws.com',    
    'region' => '<enter_your_region>',
    'key' => '<enter_your_key>',
    'secret' => '<enter_your_secret>',
);

?>