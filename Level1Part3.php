<?php

const NOT_FOUND = 'not found';
const BAD_REQUEST = 'bad request';

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
    if ($method === 'GET' && preg_match('/(\/sum\?nums=)(([0-9]+,)+([0-9]+))/', $uri)) {
        $bodyResponse = getBody($uri);
        outputHttpResponse(200, 'OK', getHeaders($bodyResponse), $bodyResponse);
    } else if (!preg_match('/(\/sum\?).+/', $uri)) {
        outputHttpResponse(404, 'Not Found', getHeaders(NOT_FOUND), NOT_FOUND);
    } else if (!preg_match('/(\/sum\?nums).+/', $uri)) {
        outputHttpResponse(400, 'Bad Request', getHeaders(BAD_REQUEST), BAD_REQUEST);
    }
}

// said sum to answer
function getBody($string)
{
    $stringNumbers = preg_replace('/[^0-9,]/', '', $string);
    $arrayNumber = explode(',', $stringNumbers);
    return array_sum($arrayNumber);
}

// generates a string containing headers
function getHeaders($content)
{
    $date = date('D, j M Y h:i:s e');
    $contentLength = strlen($content);
    return <<< HEADERS
Date: $date
Server: Apache/2.2.14 (Win32)
Content-Length: $contentLength
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

