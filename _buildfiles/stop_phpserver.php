<?php
/**
 * Stops a web server
 * Stops the process on the webserver.
 * The process id is being retrivied from the given filename $strFileName = $argv[1];
 */

$strFileName = $argv[1];

stopProcess($strFileName);

function stopProcess($name)
{
    $file = __DIR__ . './temp/' . $name . '.lock';
    if (is_file($file)) {
        $pid = (int) file_get_contents($file);
        if ($pid > 0) {
            // now we kill the process
            echo 'Try to kill process ' . $pid . "\n";
            if (DIRECTORY_SEPARATOR === '\\') {
                exec(sprintf('taskkill /F /T /PID %d', $pid), $output, $exitCode);
            } else {
                exec(sprintf('kill -9 %d', $pid), $output, $exitCode);
            }
            //delete the file after kill
            unlink($file);
        }
    }
}