<?php
/* example envio.php on ../root folder:

    #!/usr/bin/php
    <?php
    ob_start();
    include "asterisk-api/envio.php";
    ob_end_flush();
    main($argv);
*/

error_reporting(1);

echo "ini";
include "asterisk.php";
echo "passou";
function main($array_files){

    $asterisk = new asterisk($array_files);
    return $asterisk->control();
}
main(array('aa','bb'));

?>
