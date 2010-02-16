#!/usr/bin/env php
<?php

define('BASEDIR',dirname(__FILE__));
require_once(BASEDIR.'/classes/Facade/ClassLoader.php');

$classLoader = new Facade_ClassLoader();
$classLoader->includePaths(array(BASEDIR.'/classes'))->register();

// show help
if(in_array('-h', $argv) || in_array('--help', $argv) || count($argv) <> 2)
{
	echo "gets a file via http, echos it\n";
	echo "\nusage: $argv[0] (url)\n\n";
	echo "\n";
	exit(1);
}

$url = $argv[1];
$fragments = parse_url($url);

$http = new Facade_Http($fragments['host'],$fragments['port']);

$response = $http
	->get($fragments['path'])
	->send();

echo $response->getStream()->toString();
