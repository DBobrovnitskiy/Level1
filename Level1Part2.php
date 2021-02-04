<?php

// не обращайте на эту функцию внимания
// она нужна для того чтобы правильно считать входные данные
function readHttpLikeInput()
{
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
echo(json_encode($http, JSON_PRETTY_PRINT));





