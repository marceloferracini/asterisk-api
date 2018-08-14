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
include_once("phpagi-2.20/phpagi.php");


function main(){

ob_start("xxx");

//include_once("phpagi-2.20/phpagi.php");

         // Instanciando o AGI
         $agi = new AGI();

 //        $testevar = $agi->get_variable('$argv[1]');

         //$agi->exec("NOOP", "VALOR\ DE\ TESTEVAR:\ $testevar[data]");

         //Se quiser fazer debug da variavel utilize o parametro abaixo
         // e defina o debug=true no arquivo /etc/asterisk/phpagi.conf
         //foreach($testevar as $i => $value){
                 //$agi->conlog("VALOR[$i] $testevar[$i]");
         //}

        // $agi->set_variable("TESTEVAR2", "MARCELO_MUDOU");

echo "\n";

$agi->exec("NOOP", "VALOR\ recebido:\ $argv[1]");

echo "\n";

$agi->exec("NOOP", "VALOR\ recebido:\ $argv[2]");

echo "\n";


$url = system("curl http://www.meupro.com.br/teste.php \n");

//$filename = substr($url, strripos($url,"/"), strlen($url) ); 



system("wget ".$url." -O /var/lib/asterisk/sounds/".$argv[2].".wav");

system("chmod 777 /var/lib/asterisk/sounds/".$argv[2].".wav");

//system("sox ret.wav -r 8k -c 1 -e gsm ret.wav");

$resposta = $argv[2];

//echo $resposta;

ob_end_flush();

echo "\n";

$agi->set_variable("resposta", $resposta);

exit(1);

}

?>
