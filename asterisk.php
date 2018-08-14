<?php

include "Iasterisk.php";
require __DIR__ . '/vendor/autoload.php';


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


        $resposta = $argv[2];


        ob_end_flush();


        echo "\n";

        $agi->set_variable("resposta", $resposta);

        exit(1);

    }
}