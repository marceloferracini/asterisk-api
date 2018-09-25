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

exten => 123,n,agi(asterisk-api/route.php,speechToText,${pergunta},${CALLERID(num)})
exten => 123,n,NoOP(O usuario falou:  ${resposta})

exten => 123,n,agi(asterisk-api/route.php,default,${pergunta},${CALLERID(num)})
exten => 123,n,NoOP(VOLTOU ESTA INFO DO ARQUIVO PHP ${resposta})
exten => 123,n,Playback(${resposta})

exten => 123,n,agi(asterisk-api/route.php,textToSpeech,"Teste de tradução")
exten => 123,n,Playback(${resposta})

exten => 123,n,agi(asterisk-api/route.php,callIntenction,"Começar")
exten => 123,n,Playback(${resposta})

 * // use example in cli
 * //main(array('/tmp/2001', '/tmp/2001', '/tmp/2001'));
 *
 *
 * use the follows command to create the table on DB:
 * php route.php setupDB
 *
 * error_reporting(E_ALL);
 * ini_set('display_errors', TRUE);
 * ini_set('display_startup_errors', TRUE);
 */
ob_start();

include "Jobs/Asterisk.php";

function main($arrayArgv){

    array_shift($arrayArgv);

    $asterisk = new Asterisk( $arrayArgv );

    switch($arrayArgv[0]){

        case 'setupDB':
            echo $asterisk->setupDB();
            break;

        case 'getDefaultMessages':
            echo $asterisk->getDefaultMessages();
            break;

        case 'textToSpeech':
            return $asterisk->extTextToSpeech( $arrayArgv[1] );

        case 'speechToText':
            return $asterisk->extSpeechToText();

        case 'callIntenction':
            return $asterisk->callIntenction( $arrayArgv[1]);

        case 'yesno':
            return $asterisk->yesNo( 'decisao' ); 

        default:
            return $asterisk->control();

    }  
}

ob_end_flush();
main($argv);
//main(array('', 'getDefaultMessages', 'oi', '/tmp/2001'));
//main(array('', 'callIntenction', 'oi', '/tmp/2001'));
//main(array('', 'yesno', '/tmp/2001', '/tmp/2001'));
//main(array('','textToSpeech',"Certo, Aguarde só um momentinho que vou verificar"));
?>
