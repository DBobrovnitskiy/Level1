<?php

$fileName = 'counter.txt';
$counter = file_exists($fileName) ? file_get_contents($fileName) : 0;
echo "<h1 style=\"color:darkgreen\">$counter</h1>";
file_put_contents($fileName, ++$counter);