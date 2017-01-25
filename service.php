<?php

require 'functions.php';
require 'lib/nusoap.php';

$server = new nusoap_server();
$server->configureWSDL("nusoap.local", "urn:api");


$server->register("fa.getTov", [
   "jrlid" => 'xsd:integer',
   "tovnum" => 'xsd:integer',
   "cross" => 'xsd:string',
   "login" => 'xsd:string',
   "password" => 'xsd:string'
], [
    "return" => 'xsd:Array'
]);

$server->register("fa.getTov1", [
    "jrlid" => 'xsd:integer',
    "tovnum" => 'xsd:integer',
    "brand" => 'xsd:string',
    "cross" => 'xsd:string',
    "login" => 'xsd:string',
    "password" => 'xsd:string'
], [
    "return" => 'xsd:Array'
]);

$server->register("fa.getTov2", [
    "jrlid" => 'xsd:integer',
    "keytov" => 'xsd:integer',
    "cross" => 'xsd:string',
    "login" => 'xsd:string',
    "password" => 'xsd:string'
], [
    "return" => 'xsd:Array'
]);

// $server->register("fa.checkAuth", [
//     "jrlid" => 'xsd:integer',
//     "login" => 'xsd:string',
//     "password"=>'xsd:string',
// ], [
//     "return" => 'xsd:Array'
// ]);


$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
