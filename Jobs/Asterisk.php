<?php

include "IAsterisk.php";
include "DialogFlow.php";

require __DIR__ . '/../vendor/autoload.php';

use \Curl\Curl;



class Asterisk implements IAsterisk
{
    private $file_name;
    private $file_path;
    private $agi;
    private $curl;
    private $dotenv;

    //config with default vars
    private $min_confidence = 0.80;
    private $message_not_understand = 'Desculpe, não consegui te endender, poderia repetir?';

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
        $this->dotenv = new Dotenv\Dotenv(__DIR__ . "/../");
        $this->dotenv->load();
    }

    /**
     * asterisk destruct.
     * @return NULL
     *
     */
    public function __destruct()
    {

        $this->curl->close();

    }

    /**
     * This function control the comunication between Asterisk and Astrid
     *
     * @return int|mixed
     */
    public function control()
    {

        $time_start = microtime(true);

        //translate audio to text
        $message = $this->speechToText( $this->file_path );

        $time_end = microtime(true);


        $this->agi->exec("NOOP", "Total\ Execution\ Time\ speechToText:\ " .  (($time_end - $time_start)) );

        $time_start = microtime(true);

        if($message['status'] == 1){

            //send text to astrid-api
            $astrid_answer = $this->callAstrid($message['transcript']);

            $time_end = microtime(true);

            $this->agi->exec("NOOP", "Total\ Execution\ Time\ callAstrid:\ " .  (($time_end - $time_start)) );

            $time_start = microtime(true);

            //translate text to audio
            $ret['transcript'] = $this->textToSpeech( $astrid_answer );


            $time_end = microtime(true);


            $this->agi->exec("NOOP", "Total\ Execution\ Time\ textToSpeech:\ " .  (($time_end - $time_start)) );

        }else{

            $ret = $this->textToSpeech( $this->message_not_understand );

        }

        /*

                echo  print_r($this->textToSpeech('isto é um teste'),true);

                echo "<hr>";

                echo $this->speechToText('/var/www/html/teste.wav');


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

        */
        echo "\n";

        $this->agi->set_variable("resposta", $ret['transcript']);

        return 1;

    }

    /**
     * This function will call the Astrid API to get the Dialogflow answer
     *
     * @param $message
     * @return string
     */
    public function callAstrid($message)
    {
        //($projectId, $text, $sessionId, $languageCode = 'pt-BR')
        echo Dialogflow::detectIntentTexts('astrid-5a294',$message, '1');

        $ret = "";
        return $ret;
    }

    /**
     * call the api to do the text to speech translation
     *
     * @param string $message
     *
     * @return array
     *
     */
    public function textToSpeech($message)
    {

        $this->curl->get( getenv("TRANSLATE-API-URL") . '/text-to-speech', array(
                                                                            "message" => $message,       
                                                                           ));
        if ($this->curl->error) {
            
            $ret['transcript'] = 'Error: ' . $this->curl->errorCode . ': ' . $this->curl->errorMessage . "\n";
            $ret['status'] = 0;

        }else{

            $ret['transcript'] = $this->curl->response;
            $ret['status'] = 1;
        }
        
        return $ret;

    }

   /**
     * call the api to do the speech to text translation
     *
     * @param string $audio_path
     *
     * @return array
     *
     */
    public function speechToText($audio_path)
    {

        $this->curl->setOpt("CURLOPT_POSTFIELDS",true);
        $this->curl->setHeader('Content-Type', 'multipart/form-data');
        $this->curl->post( getenv("TRANSLATE-API-URL") . '/speech-to-text', array(
                                                                                  "audio" => "@" .  $audio_path,   
                                                                           ));

        if ($this->curl->error) {
            
            $ret['transcript'] = 'Error: ' . $this->curl->errorCode . ': ' . $this->curl->errorMessage . "\n";
            $ret['status'] = 0;



        }else{

            $ret = get_object_vars($this->curl->response);

            if($ret['confidence'] >= $this->min_confidence ){

                $ret['status'] = 1;

            }else{

                $ret['transcript'] = "Error, confidence too low: " . $ret['confidence'];
                $ret['status'] = 0;

            }
        }

        return $ret;

    }

}