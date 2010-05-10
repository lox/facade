#!/usr/bin/env php
<?php

define('BASEDIR',dirname(__FILE__));
require_once(BASEDIR.'/classes/Facade/ClassLoader.php');

$classLoader = new Facade_ClassLoader();
$classLoader->includePaths(array(BASEDIR.'/classes'))->register();

// show help
if(in_array('-h', $argv) || in_array('--help', $argv) || count($argv) <> 3)
{
	echo "uploads a file to S3, then downloads it again.\n";
	echo "\nusage: $argv[0] (bucket) (filename)\n\n";
	echo "\n";
	exit(1);
}

// s3 auth details are in the shell env
if(!isset($_ENV['AWS_ACCESS_KEY_ID']) || !isset($_ENV['AWS_SECRET_ACCESS_KEY']))
{
	die("AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY must be set in shell environment");
}

$file = $argv[2];
$bucket = $argv[1];
$objectName = basename($file);

$s3 = new Facade_S3(
	$_ENV['AWS_ACCESS_KEY_ID'],
	$_ENV['AWS_SECRET_ACCESS_KEY']
	);

$response = $s3
	->put(sprintf("/%s/%s",$bucket,$objectName))
	->setStream(Facade_Stream::fromFile($file))
	->setContentType('image/jpeg')
	->setHeader('Content-MD5: '.base64_encode(md5_file($file, true)))
	->send();

$response = $s3
	->get(sprintf("/%s/%s",$bucket,$objectName))
	->send();

if(strlen($response->getStream()->toString()) != filesize($file))
{
	die("response size doesn't match sent size");
}
else
{
	printf("wrote %d bytes\n",filesize($file));
}
