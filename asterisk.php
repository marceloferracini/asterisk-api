<?php

include "Iasterisk.php";
require __DIR__ . '/vendor/autoload.php';

use \Curl\Curl;

class asterisk implements Iasterisk
{
    private $file_name;
    private $file_path;
    private $agi;
    private $curl;
    private $dotenv;

    /**
     * asterisk constructor.
     * @param $array_file
     *
     */
    public function  __construct($array_file)
    {
        $this->file_path = $array_file[1];
        $this->file_name = $array_file[2];

        // creating AGI object
        $this->agi = new AGI();

        // creating curl object
        $this->curl = new Curl();

        //load .env file
        $this->dotenv = new Dotenv\Dotenv(__DIR__);
        $this->dotenv->load();
    }

    /**
     * asterisk destruct.
     * @return NULL
     *
     */
    public function __destruct(){

        $this->curl->close();

    }


    /**
     *
     * I will change all this function, for now is only a test
     *
     */
    public function control()
    {

        //translate audio to text
       // echo $this->textToSpeech('isto é um teste');
        echo "<hr>";
        echo $this->speechToText('/Users/lancedon/teste.wav');


        //send text to astrid-api

        //translate text to audio

        //set retorno to asterisk

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

    /**
     * call the api to do the text to speech translation
     *
     * @param string $message
     *
     * @return string
     *
     */
    private function textToSpeech($message)
    {

        $this->curl->get( getenv("TRANSLATE-API-URL") . '/text-to-speech', array(
                                                                            "message" => $message,       
                                                                           ));
        if ($this->curl->error) {
            
            $ret = 'Error: ' . $this->curl->errorCode . ': ' . $this->curl->errorMessage . "\n";

        }else{

            $ret = $this->curl->response;
        }
        
        return $ret;

    }

   /**
     * call the api to do the speech to text translation
     *
     * @param string $audio_path
     *
     * @return string
     *
     */
    private function speechToText($audio_path)
    {
            $this->curl->setOpt("CURLOPT_POSTFIELDS",true);


         $this->curl->setHeader('Content-Type', 'multipart/form-data');

/*         

         $this->curl->post( getenv("TRANSLATE-API-URL") . '/speech-to-text', array(
                                                                                  "audio" => "@" .  $audio_path,   
                                                                           ));
       
          
"audio" => new CURLFile( $audio_path ) , 
*/
        $this->curl->post( 'http://www.meupro.com.br/teste.php', array(
                                                                            "audio" => new CURLFile( $audio_path ) ,       
                                                                           ));


        if ($this->curl->error) {
            
            $ret = 'Error: ' . $this->curl->errorCode . ': ' . $this->curl->errorMessage . "\n";

        }else{

            $ret = $this->curl->response;
        }
        
        return $ret;

    }

}