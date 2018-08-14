<?php
/*
#!/usr/bin/php
<?php
ob_start();
include "asterisk-api/envio.php";
ob_end_flush();
main($argv);
*/

error_reporting(0);
//include_once("phpagi-2.20/phpagi.php");

include "asterisk.php";

function main($argv){

    $asterisk = new asterisk($argv);
    $asterisk->control();
}

?>
