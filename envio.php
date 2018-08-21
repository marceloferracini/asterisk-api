<?php
/* example envio.php on ../root folder:

    #!/usr/bin/php
    <?php
    ob_start();
    include "asterisk-api/envio.php";
    ob_end_flush();
    main($argv);
*/

/*
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
*/

include "Jobs/Asterisk.php";

function main($array_files){

    $asterisk = new Asterisk($array_files);
    return $asterisk->control();
}
//main(array('/tmp/2001', '/tmp/2001', '/tmp/2001'));

//main(array('/Users/leandro/Desktop/teste.wav', '/Users/leandro/Desktop/teste.wav', '/Users/leandro/Desktop/teste.wav'));

?>
