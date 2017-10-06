<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read https://symfony.com/doc/current/setup.html#checking-symfony-application-configuration-and-setup
// for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
$whiteListedAddresses = ['127.0.0.1', 'fe80::1', '::1', 'at-port', '192.168.1.16'];
if (isset($_SERVER['DOCKER_BRIDGE_IP'])) {
    $whiteListedAddresses[] = $_SERVER['DOCKER_BRIDGE_IP'];
}

if ('varnish' !== $proxyIp = gethostbyname('varnish')) {
    $whiteListedAddresses[] = $proxyIp;
} else {
    $proxyIp = false;
}

if (isset($_SERVER['HTTP_CLIENT_IP'])
    || (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ($proxyIp !== @$_SERVER['REMOTE_ADDR'] || !in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $whiteListedAddresses)))
    || !(in_array(@$_SERVER['REMOTE_ADDR'], $whiteListedAddresses) || php_sapi_name() === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

require __DIR__.'/../vendor/autoload.php';
Debug::enable();

$kernel = new AppKernel('test', true);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
