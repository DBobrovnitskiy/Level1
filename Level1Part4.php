<?php

const NOT_FOUND = 'not found';
const SERVER_ERROR = 'internal server error';
const UNAUTHORIZED = 'unauthorized';
const FILE_NAME = 'passwords.txt';

function readHttpLikeInput()
{
    // ну это уже написано за вас
    $f = fopen('php://stdin', 'r');
    $store = "";
    $toread = 0;
    while ($line = fgets($f)) {
        $store .= preg_replace("/\r/", "", $line);
        if (preg_match('/Content-Length: (\d+)/', $line, $m))
            $toread = $m[1] * 1;
        if ($line == "\r\n")
            break;
    }
    if ($toread > 0)
        $store .= fread($f, $toread);
    return $store;
}

$contents = readHttpLikeInput();

function outputHttpResponse($statuscode, $statusmessage, $headers, $body)
{
    echo "HTTP/1.1 $statuscode $statusmessage\n$headers\n$body";
}

//generates a response and displays the result
function processHttpRequest($method, $uri, $headers, $body)
{
    if ($uri !== '/api/checkLoginAndPassword' || !chekContentType($headers)) {
        outputHttpResponse(404, 'Not Found', getHeaders(NOT_FOUND), NOT_FOUND);
    } else if (!file_exists(FILE_NAME)) {
        outputHttpResponse(500, 'Internal Server Error', getHeaders(SERVER_ERROR), SERVER_ERROR);
    } else {
        formAResponse($body, file_get_contents(FILE_NAME));
    }
}

//on the data base from the file "passwords.txt" outputs the answer in the form of html code, or 401 errors
function formAResponse($nameAndPassword, $passwords)
{
    $nameAndPassword = preg_replace('/(login=)|(password=)/', '', $nameAndPassword);
    $nameAndPassword = str_replace('&', ':', $nameAndPassword);
    $array = explode("\n", $passwords);
    foreach ($array as $stringLine) {
        if ($stringLine === $nameAndPassword) {
            $body = '<h1 style="color:green">FOUND</h1>';
            outputHttpResponse(200, 'OK', getHeaders($body), $body);
            return;
        }
    }
    outputHttpResponse(401, 'Unauthorized', getHeaders(UNAUTHORIZED), UNAUTHORIZED);
}

//checks if "Content-Type" contains a "application/x-www-form-urlencoded"
function chekContentType($array): bool
{
    foreach ($array as $header) {
        if ($header[0] === 'Content-Type') {
            return $header[1] === 'application/x-www-form-urlencoded';
        }
    }
    return false;
}

// generates a string containing headers
function getHeaders($string)
{
    $date = date('D, j M Y h:i:s e');
    $bodyLength = strlen($string);
    return <<< HEADERS
Date: $date
Server: Apache/2.2.14 (Win32)
Content-Length: $bodyLength
Connection: Closed
Content-Type: text/html; charset=utf-8

HEADERS;
}

//Splits the string on method, uri, headers, and body
function parseTcpStringAsHttpRequest($string)
{
    $arrayStrings = explode("\n", $string);
    return array(
        "method" => strtok(array_shift($arrayStrings), ' '),
        "uri" => strtok(' '),
        "headers" => createHeaders($arrayStrings),
        "body" => array_pop($arrayStrings),
    );
}

//forms an array of arrays that contain headers data
function createHeaders(&$array)
{
    $newHeaders = array();
    foreach ($array as $i => $value) {
        if (strpos($value, ':')) {
            $newHeaders[] = array(strtok($value, ':'), ltrim(strtok('')));
            unset($array[$i]);
        }
    }
    return $newHeaders;
}

$http = parseTcpStringAsHttpRequest($contents);
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);


