<?php

include "Iasterisk.php";
require __DIR__ . '/vendor/autoload.php';


class asterisk implements Iasterisk
{
    private $file_name;
    private $file_path;
    private $agi;

    /**
     * asterisk constructor.
     * @param $array_file
     *
     */
    public function  __construct($array_file)
    {
        $this->file_path = $array_file[1];
        $this->file_name = $array_file[2];

        // creating a AGI instance
        $this->agi = new AGI();
    }


    /**
     *
     * I will change all this function, for now is only a test
     *
     */
    public function control()
    {





        ob_start("xxx");

        echo "\n";

        $this->agi->exec("NOOP", "VALOR\ recebido:\ " .  $this->file_name);

        echo "\n";

        $this->agi->exec("NOOP", "VALOR\ recebido:\ " .  $this->file_path);

        echo "\n";

        $url = system("curl http://www.meupro.com.br/teste.php \n");

        //$filename = substr($url, strripos($url,"/"), strlen($url) );


        system("wget " . $url . " -O /var/lib/asterisk/sounds/" .  $this->file_name . ".wav");

        system("chmod 777 /var/lib/asterisk/sounds/" .  $this->file_name . ".wav");


        $resposta = $this->file_name;


        ob_end_flush();


        echo "\n";

        $this->agi->set_variable("resposta", $resposta);

        return 1;

    }
}