<?php 

return array (
    
    // LHC has three different file types
    // lhc-images are always public
    // lhc-files and lhc-form-files are protected
	'images_path' => 'lhc-images',
	'files_path' => 'lhc-files',
	'form_path' => 'lhc-form-files',
    
    // Bucket name
    'lhc_bucket' => getenv('S3_BUCKET') ?: 'lhc-cloud',
    
    // Authentification
    's3_files_host' => getenv('S3_HOST') ?: 'https://s3-eu-west-1.amazonaws.com',    
    'region' => getenv('S3_REGION') ?: '', # enter your region
    'key' => getenv('S3_KEY') ?: '', # enter your key
    'secret' => getenv('S3_SECRET') ?: '', # enter your secret
);

?>
