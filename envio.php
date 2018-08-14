<?php
/*
#!/usr/bin/php
<?php
ob_start("xxx");
include "asterisk-api/envio.php";
ob_end_flush();
main();
*/

error_reporting(0);
//include_once("phpagi-2.20/phpagi.php");

include "asterisk.php";

function main(){

    $asterisk = new asterisk($argv);
    $asterisk->control();
}

?>
