<?php
require 'vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

// Use absolute path to JSON credentials in root
$storage = new StorageClient([
    'projectId' => 'fundify-474122',
    'keyFilePath' => __DIR__ . '/fundify-474122.json'
]);

$bucketName = 'fundify-media-bucket';
$bucket = $storage->bucket($bucketName);
?>
