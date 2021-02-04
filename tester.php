
<?php

/*
array(3) {
  [0]=>
  string(10) "tester.php"
  [1]=>
  string(1) "1"
  [2]=>
  string(19) "test-impl/task1.php"
}
*/
if (count($argv) != 3) {
	die("usage: php tester.php <tasknum> <path/to/task.php>\nexample: php tester.php 1 t1.php\n");
}

$inputs = array(
	1 => <<<T1
hello
T1
,
	2 => <<<T2
POST /doc/test HTTP/1.1
Host: shpp.me
Accept: image/gif, image/jpeg, */*
Accept-Language: en-us
Accept-Encoding: gzip, deflate
User-Agent: Mozilla/4.0
Content-Length: 35

bookId=12345&author=Tan+Ah+Teck
T2
,
	3 => <<<T3
GET /sum?nums=1,2,3,4 HTTP/1.1
Host: shpp.me
Accept: image/gif, image/jpeg, */*
Accept-Language: en-us
Accept-Encoding: gzip, deflate
User-Agent: Mozilla/4.0
T3
,
	4 => <<<T4
POST /api/checkLoginAndPassword HTTP/1.1
Accept: */*
Content-Type: application/x-www-form-urlencoded
User-Agent: Mozilla/4.0
Content-Length: 35
login=student&password=12345
T4
,
	5 => <<<T5
GET / HTTP/1.1
Host: student.shpp.me
Accept: image/gif, image/jpeg, */*
Accept-Language: en-us
Accept-Encoding: gzip, deflate
User-Agent: Mozilla/4.0
T5
);

// =============================================== ANSWERS

$answers = array(
	1 => <<<T1
3
T1
,
	2 => <<<T2
{
    "method": "POST",
    "uri": "\/doc\/test",
    "headers": [
        [
            "Host",
            "shpp.me"
        ],
        [
            "Accept",
            "image\/gif, image\/jpeg, *\/*"
        ],
        [
            "Accept-Language",
            "en-us"
        ],
        [
            "Accept-Encoding",
            "gzip, deflate"
        ],
        [
            "User-Agent",
            "Mozilla\/4.0"
        ],
        [
            "Content-Length",
            "35"
        ]
    ],
    "body": "bookId=12345&author=Tan+Ah+Teck"
}
T2
,
	3 => <<<T3
HTTP/1.1 200 OK
Server: Apache/2.2.14 (Win32)
Content-Length: 2
Connection: Closed
Content-Type: text/html; charset=utf-8

10
T3
,
	4 => <<<T4
HTTP/1.1 200 OK
Server: Apache/2.2.14 (Win32)
Content-Length: 34
Connection: Closed
Content-Type: text/html; charset=utf-8

<h1 style="color:green">FOUND</h1>
T4
,
	5 => <<<T5
GET / HTTP/1.1
Host: student.shpp.me
Accept: image/gif, image/jpeg, */*
Accept-Language: en-us
Accept-Encoding: gzip, deflate
User-Agent: Mozilla/4.0
T5
);


$descriptorspec = array(
   0 => array("pipe", "r"),
   1 => array("pipe", "w")
);

$process = proc_open("php ".$argv[2], $descriptorspec, $pipes);

if (is_resource($process)) {

    fwrite($pipes[0], $inputs[$argv[1]]);
    fclose($pipes[0]);

    $content = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $retval = proc_close($process);

    echo "-----YOUR-RESPONSE-BEGIN--------------------\n";
    echo "(".$content.")\n";
    echo "-----YOUR-RESPONSE-END----------------------\n\n";
    //echo "retval=$retval\n\n";

    // remove date header Date: Sun, 18 Oct 2012 10:36:20 GMT
    $c2 = preg_replace("/Date: [^\n]+\n/", "", $content);
    $c2 = preg_replace("/\r/", "", $c2);
    if ($c2 == $answers[$argv[1]]) {
    	echo "response is correct :)";
    }
    else {
	    echo "-----CORRECT-RESPONSE-BEGIN--------------------\n";
	    echo "(".$answers[$argv[1]].")\n";
	    echo "-----CORRECT-RESPONSE-END----------------------\n\n";
	    echo strlen($c2)." vs ".strlen($answers[$argv[1]])."\n";

    	echo "response is incorrect! SEE ABOVE\n";
    }
}
