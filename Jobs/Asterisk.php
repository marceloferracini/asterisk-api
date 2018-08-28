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
    private $message_not_understand = 'not_understand';

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
     * create all tables and fill it with the default value
     */
    public function setupDB()
    {

        //put here all table files, like migrations on Laravel
        require_once "database/AllDefaultMessages.php";

        //fill default values
        $defaults[] = array('textName'  => 'MENS_AGUARDE',
                            'textValue' => 'Certo, Aguarde só um momentinho que vou verificar');
        $defaults[] = array('textName'  => 'MENS_NAO_ENTENDI',
                            'textValue' => 'Nao entendi sua pergunta, poderia repetir?');
        $defaults[] = array('textName'  => 'MENS_NAO_CONSEGUI_AJUDAR',
                            'textValue' => 'Infelizmente nao conseguirei te ajudar, entre em contato em horario comercial ou acesse nosso site www.astrocentro.com.br, obrigado');
        $defaults[] = array('textName'  => 'MENS_DUVIDA',
                            'textValue' => 'Consegui tirar sua dúvida, diga sim ou não ');
        $defaults[] = array('textName'  => 'MENS_CONSEGUI_AJUDAR',
                            'textValue' => 'Fico muito feliz em ter ajudado, caso tenha novas dúvidas nos contate, ficaremos felizes em te atender. Forte abraço!!');

        //store on DB
        foreach ($defaults as $default)  AllDefaultMessages::Create($default);

    }

    /**
     * set all default messages from DB
     */
    public function getDefaultMessages()
    {

        require_once __DIR__ . "/../bootstrap.php";

        $this->agi->exec("NOOP", "getDefaultMessages\ ");

        $time_start = microtime(true);

        foreach (AllDefaultMessages::All() as $message) {

            //translate text to audio
            $ret = $this->textToSpeech( $message->textValue );

            $ret['localFile'] = $this->convertFileToAsterisk($ret['transcript'], $ret['fileName']);

            $this->agi->exec("NOOP",  $message->textName . "\ ". $ret['localFile']);

            echo "\n";

            $this->agi->set_variable($message->textName, $ret['localFile'] );

        }

        $time_end = microtime(true);

        $this->agi->exec("NOOP", "Total\ Execution\ Time\ getDefaultMessages:\ " .  (($time_end - $time_start)) );

    }

    /**
     * This function control the comunication between Asterisk and Astrid
     *
     * @return int|mixed
     */
    public function control()
    {

        $this->agi->exec("NOOP", "control\ ");

        $time_start = microtime(true);

        //translate audio to text
        $message = $this->speechToText( $this->file_path . ".wav");

	    $this->agi->exec("NOOP", "Message\ " . $message['transcript'] );

        $time_end = microtime(true);

        $this->agi->exec("NOOP", "Total\ Execution\ Time\ speechToText:\ " .  (($time_end - $time_start)) );


        $time_start = microtime(true);

        if($message['status'] == 1){

            //send text to astrid-api
            $astrid_answer = $this->callAstrid($message['transcript']);

            $time_end = microtime(true);

	        $this->agi->exec("NOOP", "AstridAnswer:\ " . $astrid_answer );

            $this->agi->exec("NOOP", "Total\ Execution\ Time\ callAstrid:\ " .  (($time_end - $time_start)) );

            $time_start = microtime(true);


            echo "\n";

            //to avoid not understand answers
            if($astrid_answer == 'Desculpe, mas não consegui te entender' || !$astrid_answer){

                $this->agi->set_variable("not_understand", 1 );
                return 1;

            }else{

                $this->agi->set_variable("not_understand", 0 );

            }

            echo "\n";

            //translate text to audio
            $ret = $this->textToSpeech( $astrid_answer );

            $time_end = microtime(true);

            $this->agi->exec("NOOP", "Total\ Execution\ Time\ textToSpeech:\ " .  (($time_end - $time_start)) );

        }else{

            //$ret = $this->textToSpeech( $this->message_not_understand );
	    $ret['localFile'] = $this->message_not_understand;

        }

        $ret['localFile'] = $this->convertFileToAsterisk($ret['transcript'], $ret['fileName']);

        echo "\n";

        $this->agi->set_variable("resposta", $ret['localFile'] );

        return 1;

    }

    /**
     * This function control the comunication between Asterisk and Astrid
     *
     * @param string $message
     * @return int|mixed
     */
    public function callIntenction($message = 'Começar')
    {

        $this->agi->exec("NOOP", "callIntenction\ ");

        $time_start = microtime(true);


        //send text to astrid-api
        $astrid_answer = $this->callAstrid($message);

        $time_end = microtime(true);

        $this->agi->exec("NOOP", "AstridAnswer:\ " . $astrid_answer );

        $this->agi->exec("NOOP", "Total\ Execution\ Time\ callAstrid:\ " .  (($time_end - $time_start)) );

        $time_start = microtime(true);


        //to avoid null answers
        if(!$astrid_answer)
            $astrid_answer = $this->message_not_understand;

        //translate text to audio
        $ret = $this->textToSpeech( $astrid_answer );

        $time_end = microtime(true);

        $this->agi->exec("NOOP", "Total\ Execution\ Time\ textToSpeech:\ " .  (($time_end - $time_start)) );



        $ret['localFile'] = $this->convertFileToAsterisk($ret['transcript'], $ret['fileName']);

        echo "\n";

        $this->agi->set_variable("resposta", $ret['localFile'] );

        return 1;

    }

    /**
     * This function will receive a string and return the audio in Asterisk format
     * in case of error will return a default audio message
     *
     * @param string $message
     * @return string
     */
    public function extTextToSpeech($message)
    {

        $this->agi->exec("NOOP", "extTextToSpeech\ ");

        $time_start = microtime(true);

        //translate text to audio
        $ret = $this->textToSpeech( $message );


 	    $this->agi->exec("NOOP", "extTextToSpeech\ " . $message );
        $this->agi->exec("NOOP", "extTextToSpeech\ " . $ret['transcript'] );


        $time_end = microtime(true);

        $this->agi->exec("NOOP", "Total\ Execution\ Time\ textToSpeech:\ " .  (($time_end - $time_start)) );


        $ret['localFile'] = $this->convertFileToAsterisk($ret['transcript'], $ret['fileName']);

	    $this->agi->exec("NOOP", "extTextToSpeech\ ". $ret['localFile']);

        echo "\n";

        $this->agi->set_variable("resposta", $ret['localFile'] );

        return 1;

    }
    
    /**
     * This function will receive a audio file and return the text of the audio, 
     * in case of error will return a default message
     *
     * @return string
     */
    public function extSpeechToText()
    {

        $this->agi->exec("NOOP", "extSpeechToText\ ");
        
        $time_start = microtime(true);

        //translate audio to text
        $message = $this->speechToText( $this->file_path . ".wav");

        $this->agi->exec("NOOP", "Message\ " . $message['transcript'] );

        $time_end = microtime(true);

        $this->agi->exec("NOOP", "Total\ Execution\ Time\ speechToText:\ " .  (($time_end - $time_start)) );


        $time_start = microtime(true);

        if($message['status'] == 1){

            $ret = $message['transcript'];

        }else{

            $ret = $this->message_not_understand;

        }

        echo "\n";

        $this->agi->set_variable("resposta", $ret);

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
        $ret = Dialogflow::detectIntentTexts('astrid-5a294',$message, '1');

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

        $this->agi->exec("NOOP", "textToSpeech\ " . $message);

        $this->curl->get( getenv("TRANSLATE-API-URL") . '/text-to-speech', array(
                                                                            "message" => $message,       
                                                                           ));
        if ($this->curl->error) {
            
            $ret['transcript'] = 'Error:\ ' . $this->curl->errorCode . '\: ' . $this->curl->errorMessage . "\ \n";
            $ret['status'] = 0;

        }else{

            $ret['transcript'] = $this->curl->response;
            $ret['fileName'] =  substr($ret['transcript'], strrpos($ret['transcript'], '/')+1);
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

    /**
     * This function convert the mp3 audio file to wav with 8000 hz audio file
     *
     * @param string $s3Url
     * @param string $fileName
     *
     * @return mixed
     */
    public function convertFileToAsterisk($s3Url, $fileName)
    {

        $FileNameWithOutExt = substr($fileName, 0, strpos($fileName, '.'));

        if(!file_exists("/tmp/" . $FileNameWithOutExt . ".wav" )){

            $audio = file_get_contents($s3Url);

            //save file on /tmp
            file_put_contents("/tmp/" . $fileName, $audio);

            system("lame --decode /tmp/$fileName - | sox -v 0.5 -t wav - -t wav -b 16 -r 8000 -c 1 /tmp/$FileNameWithOutExt.wav");
        }

        return "/tmp/" . $FileNameWithOutExt;

    }
}
