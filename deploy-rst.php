#!/usr/bin/env php
<?php
require_once __DIR__ . '/config.php';

function err($msg)
{
    file_put_contents("php://stderr", $msg . "\n");
}

function run($cmd)
{
    $handle = popen($cmd, 'r');
    if (!is_resource($handle)) {
        return array('', 1);
    }

    $output = stream_get_contents($handle);
    $retval = pclose($handle);

    return array($output, $retval);
}

if ($argc <= 1) {
    err('Please pass a file name');
    exit(1);
}
$file = $argv[1];
if (!file_exists($file)) {
    err('File does not exist');
    exit(2);
}

require_once 'System.php';

$cmdRst2c = System::which('rst2confluence');
if ($cmdRst2c === false) {
    err('rst2confluence not found');
    exit(2);
}
$cmdCli = System::which('confluence-cli');
if ($cmdCli === false) {
    err('confluence-cli not found');
    exit(2);
}

list($rcDoc, $retval) = run($cmdRst2c . ' ' . escapeshellarg($file));
if ($retval !== 0) {
    err('Error converting rst to confluence format');
    exit(20);
}

//we cannot pipe it, see https://studio.plugins.atlassian.com/browse/CSOAP-122
$tmpfile = tempnam(sys_get_temp_dir(), 'deploy-confluence-');
$cmd = sprintf(
    'confluence-cli --server %s --user %s --password %s --action getPageSource --space %s --title %s --file %s --quiet',
    escapeshellarg($cflHost),
    escapeshellarg($cflUser),
    escapeshellarg($cflPass),
    escapeshellarg($cflSpace),
    escapeshellarg($cflTitle),
    escapeshellarg($tmpfile)
);
list($lastline, $retval) = run($cmd);
$curDoc = file_get_contents($tmpfile);
unlink($file);

//list($curDoc, $retval) = run($cmd);
if ($retval !== 0) {
    err('Error fetching confluence document source' . "\n" . $lastline);
    exit(21);
}
if (strlen($curDoc) == 0) {
    err('Document is empty. This might be a bug');
    exit(22);
}

$begin = strpos($curDoc, $markerBegin);
$end   = strpos($curDoc, $markerEnd);

if ($begin === false && $end === false) {
    //add it to the end
    $newDoc = $curDoc . "\n\n" . $markerBegin . $rcDoc . $markerEnd;
} else if ($begin  === false || $end === false) {
    err('Begin or end marker not found');
    exit(23);
} else if ($end < $begin) {
    err('Begin marker after end marker');
    exit(24);
} else {
    //replace it
    $newDoc = substr($curDoc, 0, $begin)
        . $markerBegin . $rcDoc . $markerEnd
        . substr($curDoc, $end + strlen($markerEnd));
}


//we cannot pipe because of https://studio.plugins.atlassian.com/browse/CSOAP-121
$file = tempnam(sys_get_temp_dir(), 'deploy-confluence-');
file_put_contents($file, $newDoc);
$cmd = sprintf(
    'confluence-cli --server %s --user %s --password %s --action storePage --space %s --title %s --file %s --quiet',
    escapeshellarg($cflHost),
    escapeshellarg($cflUser),
    escapeshellarg($cflPass),
    escapeshellarg($cflSpace),
    escapeshellarg($cflTitle),
    escapeshellarg($file)
);
list($lastline, $retval) = run($cmd);
if ($retval !== 0) {
    err('Error storing new document in confluence' . "\n" . $lastline);
    exit(30);
}
?>