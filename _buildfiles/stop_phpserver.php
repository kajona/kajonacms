<?php
/**
 * Stops a web server
 * Stops the process on the webserver.
 * The process id is being retrivied from the given filename $strFileName = $argv[1];
 */

$strPathToFileName = $argv[1];
$strFileName = $argv[2];

stopProcess($strPathToFileName.$strFileName);

function stopProcess($name)
{
    $file = $name;
    if (is_file($file)) {
        $pid = (int) file_get_contents($file);
        if ($pid > 0) {
            // now we kill the process
            echo 'Try to kill process ' . $pid . "\n";
            if (DIRECTORY_SEPARATOR === '\\') {
                // check whether pid exists
                $output = array();
                exec(sprintf('tasklist /FI "PID eq %d"', $pid), $output, $exitCode);
                $content = implode("\n", $output);

                if (strpos($content, $pid) !== false) {
                    // if the pid exists try to kill it
                    $output = array();
                    exec(sprintf('taskkill /F /T /PID %d', $pid), $output, $exitCode);
                }
            } else {
                // check whether pid exists
                $output = array();
                exec(sprintf('ps -p %d', $pid), $output, $exitCode);
                $content = implode("\n", $output);

                if (strpos($content, $pid) !== false) {
                    // if the pid exists try to kill it
                    $output = array();
                    exec(sprintf('kill -9 %d', $pid), $output, $exitCode);
                }
            }
            //delete the file after kill
            unlink($file);
        }
    }
}