<?php

include "Iasterisk.php";

class asterisk implements Iasterisk
{

    public function control()
    {

        ob_start("xxx");

        // Instanciando o AGI
        $agi = new AGI();

        echo "\n";

        $agi->exec("NOOP", "VALOR\ recebido:\ $argv[1]");

        echo "\n";

        $agi->exec("NOOP", "VALOR\ recebido:\ $argv[2]");

        echo "\n";


        $url = system("curl http://www.meupro.com.br/teste.php \n");

        //$filename = substr($url, strripos($url,"/"), strlen($url) );


        system("wget " . $url . " -O /var/lib/asterisk/sounds/" . $argv[2] . ".wav");

        system("chmod 777 /var/lib/asterisk/sounds/" . $argv[2] . ".wav");

        //system("sox ret.wav -r 8k -c 1 -e gsm ret.wav");

        $resposta = $argv[2];

        //echo $resposta;

        ob_end_flush();

        echo "\n";

        $agi->set_variable("resposta", $resposta);

        exit(1);

    }
}