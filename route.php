#!/usr/bin/php
<?php
/* 
 * use example:
 * // default use
 * exten => 123,n,agi(asterisk-api/route.php,default,${pergunta},${CALLERID(num)})
 * // Text to Speech
 * exten => 123,n,agi(asterisk-api/route.php,textToSpeech,${pergunta},${CALLERID(num)})
 * // Speech to Text
 * exten => 123,n,agi(asterisk-api/route.php,speechToText,teste de tradução)
 *
 * // use example in cli
 * //main(array('/tmp/2001', '/tmp/2001', '/tmp/2001'));
 *
 * error_reporting(E_ALL);
 * ini_set('display_errors', TRUE);
 * ini_set('display_startup_errors', TRUE);
 */
ob_start();

include "Jobs/Asterisk.php";

function main($arrayArgv){

    $asterisk = new Asterisk( array_shift($arrayArgv) );

    switch($arrayArgv[0]){

        case 'textToSpeech':
            return $asterisk->extTextToSpeech($arrayArgv[1]);

        case 'speechToText':
            return $asterisk->extSpeechToText();
        
        default:
            return $asterisk->control();

    }  
}

ob_end_flush();
main($argv);
?>
