<?php
/**
 * Starts a web server
 * Additionally the PID of the server is being stored in a seperate file.
 * This is needed in order to kill the server after the build (@see stop_phpserver.php)
 */
$strServerRootPath = $argv[1];
$strServerIp = $argv[2];
$strServerPort = $argv[3];
$strPathToFileName = $argv[4];
$strFileName = $argv[5];

startProcess($strPathToFileName.$strFileName, "php -S $strServerIp:$strServerPort -t $strServerRootPath");

function startProcess($name, $cmd)
{
    echo 'Start ' . $name . ' (' . $cmd . ')' . "\n";
    $process = proc_open($cmd, array(), $pipes);
    $status  = proc_get_status($process);
    $pid  = $status['pid'];
    $file = $name;
    file_put_contents($file, $pid);
}