<?php

include "Iasterisk.php";
require __DIR__ . '/vendor/autoload.php';


class asterisk implements Iasterisk
{
    private $file_name;
    private $file_path;

    public function  __construct($array_file)
    {
        $this->file_path = $array_file[1];
        $this->file_name = $array_file[2];
    }

    public function control()
    {

        ob_start("xxx");

        // Instanciando o AGI
        $agi = new AGI();

        echo "\n";

        $agi->exec("NOOP", "VALOR\ recebido:\ " .  $this->file_name);

        echo "\n";

        $agi->exec("NOOP", "VALOR\ recebido:\ " .  $this->file_path);

        echo "\n";


        $url = system("curl http://www.meupro.com.br/teste.php \n");

        //$filename = substr($url, strripos($url,"/"), strlen($url) );


        system("wget " . $url . " -O /var/lib/asterisk/sounds/" .  $this->file_name . ".wav");

        system("chmod 777 /var/lib/asterisk/sounds/" .  $this->file_name . ".wav");


        $resposta = $this->file_name;


        ob_end_flush();


        echo "\n";

        $agi->set_variable("resposta", $resposta);

        exit(1);

    }
}