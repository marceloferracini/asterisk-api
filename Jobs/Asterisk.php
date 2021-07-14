<?php

include "IAsterisk.php";
include "DialogFlow.php";
include "Translate.php";
require __DIR__ . '/../vendor/autoload.php';

use \Curl\Curl;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Asterisk implements IAsterisk
{
    private $file_name;
    private $file_path;
    private $agi;
    private $curl;
    private $dotenv;
    public  $logger;

    //config with default vars
    private $min_confidence = 0.80;

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

        //logger
        $this->logger = new Logger('AsteriskLogger');
        $this->logger->pushHandler(new StreamHandler('/tmp/Asterisk.log', Logger::DEBUG));
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
     * Get the size of a file and return that to Asterisk (used to try identify if is a sort message or no)
     *
     * @param $file
     * @return int
     */
    public function getFileSize($file){

        $fileSize = filesize($file.".wav");

        $this->agi->exec("NOOP",  "fileSize=" .$fileSize);

        echo "\n";

        $this->agi->set_variable("fileSize", $fileSize );

        return 1;

    }

    /**
     * create all tables and fill it with the default value
     */
    public function setupDB()
    {

        //put here all table files, like migrations on Laravel
        require_once "database/AllDefaultMessages.php";

        //fill default values
        $defaults[] = array('textName'  => 'MENS_AGUARDE0',
                            'textValue' => 'Certo, Aguarde só um momentinho');
        $defaults[] = array('textName'  => 'MENS_AGUARDE1',
                            'textValue' => 'OK, só um minuto');
        $defaults[] = array('textName'  => 'MENS_AGUARDE2',
                            'textValue' => 'OK, só um instante');
        $defaults[] = array('textName'  => 'MENS_AGUARDE3',
                            'textValue' => 'Aguarde só um segundinho');
        $defaults[] = array('textName'  => 'MENS_NAO_ENTENDI',
                            'textValue' => 'Não entendi sua pergunta, poderia repetir?');
        $defaults[] = array('textName'  => 'MENS_NAO_CONSEGUI_AJUDAR',
                            'textValue' => 'Infelizmente nao conseguirei te ajudar, entre em contato com nosso uatiszap ou nosso site mamamiapizzaria ponto com ponto bê erre');
        $defaults[] = array('textName'  => 'MENS_DUVIDA',
                            'textValue' => 'Consegui te ajudar ? Estou a disposição, se tiver mais dúvidas é só falar');
        $defaults[] = array('textName'  => 'MENS_CONSEGUI_AJUDAR',
                            'textValue' => 'Fico muito feliz em ter ajudado, caso precise de mais alguma coisa a total disposição, ficaremos felizes em te atender. Forte abraço!!');
        $defaults[] = array('textName'  => 'MENS_ENTENDI',
                            'textValue' => 'Entendi, vamos tentar de outra forma então, me diga em poucas palavras o que você precisa');
        $defaults[] = array('textName'  => 'MENS_DEFAULT',
                            'textValue' => 'Nesse caso não posso ajuda-lo, peço que entre em contato com nosso uatiszap');
        $defaults[] = array('textName'  => 'MENS_DECISAO',
                            'textValue' => 'Desculpe, não consegui entender, diga novamente de forma mais clara, por favor');
        $defaults[] = array('textName'  => 'MENS_OUTRA_DUVIDA',
                            'textValue' => 'Tem mais coisa que eu consiga lhe ajuda, só falar.');

        //store on DB
        foreach ($defaults as $default)  AllDefaultMessages::Create($default);

        var_dump('Gerando os áudios padrões, dê ENTER até o final');

        $messages = AllDefaultMessages::All();

        foreach ($messages as $message) {
            
		    //translate text to audio
            $ret = $this->textToSpeechName($message->textValue, $message->textName);

	    	if($ret['status'] == 1){
                $fileRemovePath = parse_url($ret['fileName']);
                $ret['fileName'] = $fileRemovePath[path];
                
                $ret['localFile'] = $this->convertFileToAsterisk($ret['transcript'], $ret['fileName']);

                var_dump($message->textName . ">>>> ". $ret['localFile']);

            }else{

                $this->agi->exec("NOOP",  "Error on   ". $message->textName. ":::". $ret['localFile']);

            }

        }

    }

    /**
     * set all default messages from DB
     */
    public function getDefaultMessages($like = NULL) {

        require_once __DIR__ . "/../bootstrap.php";

        $this->agi->exec("NOOP", "getDefaultMessages\ ");

        $time_start = microtime(true);

	    $this->agi->exec("NOOP", "VARIAVEL_LIKE:\ " . $like );

        if($like){
            $messages = AllDefaultMessages::where('textName', 'like', $like.'%')->get();
        } else {
            $messages = AllDefaultMessages::All();
        }

	    foreach ($messages as $message) {
            
		    //translate text to audio
            $ret = $this->textToSpeech( $message->textValue );

	    	if($ret['status'] == 1){
                $ret['localFile'] = $this->convertFileToAsterisk($ret['transcript'], $ret['fileName']);

                $this->agi->exec("NOOP",  $message->textName . "\ ". $ret['localFile']);

                echo "\n";

                $this->agi->set_variable($message->textName, $ret['localFile'] );

            }else{

                $this->agi->exec("NOOP",  "Error on   ". $message->textName. ":::". $ret['localFile']);

            }

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

        require_once __DIR__ . "/../bootstrap.php";

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

	        $this->agi->exec("NOOP", "AstridAnswer:\ " . $astrid_answer['text'] );

            $this->agi->exec("NOOP", "Total\ Execution\ Time\ callAstrid:\ " .  (($time_end - $time_start)) );

            $time_start = microtime(true);


            echo "\n";

            //to avoid not understand answers
            if($astrid_answer['text'] == 'Desculpe, mas não consegui te entender' || !$astrid_answer){

                $this->agi->set_variable("not_understand", 1 );
                return 1;

            }else{

                $this->agi->set_variable("not_understand", 0 );

            }

            echo "\n";

            //not find DialogFlow answer
            if(!isset($astrid_answer['text']) || $astrid_answer['text'] == ""){
                $this->agi->exec("NOOP", "DialogFlow\ Nulls\ in\ ");

                $astrid_answer['text'] = AllDefaultMessages::where('textName', '=', 'MENS_DEFAULT')->get(array("textValue"));
                $astrid_answer['text'] = $astrid_answer['text']->toArray();
                $astrid_answer['text'] = $astrid_answer['text'][0][textValue];

                $this->agi->exec("NOOP", "DialogFlow\ Nulls\ " .  $astrid_answer['text'] );
                echo "\n";
            }

            //translate text to audio
            $ret = $this->textToSpeech( $astrid_answer['text'] );

            $time_end = microtime(true);

            $this->agi->exec("NOOP", "Total\ Execution\ Time\ textToSpeech:\ " .  (($time_end - $time_start)) );

        }else{

           $this->agi->set_variable("not_understand", 1 );
           return 1;

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
     * @param string $contextName
     * @return int|mixed
     */
    public function callIntenction($message = 'Começar', $contextName = '')
    {

        $this->agi->exec("NOOP", "callIntenction\ ");

        $time_start = microtime(true);


        //send text to astrid-api
        $astrid_answer = $this->callAstrid($message, $contextName);
        


        $time_end = microtime(true);

        $this->agi->exec("NOOP", "AstridAnswer:\ " . $astrid_answer['text'] );

        $this->agi->exec("NOOP", "Total\ Execution\ Time\ callAstrid:\ " .  (($time_end - $time_start)) );

        $time_start = microtime(true);


        //to avoid null answers
        if($astrid_answer['confidence'] < $min_confidence) {

            $this->agi->set_variable("not_understand", 1);
            return 1;

        }


        //translate text to audio
        $ret = $this->textToSpeech( $astrid_answer['text'] );

        $time_end = microtime(true);

        $this->agi->exec("NOOP", "Total\ Execution\ Time\ textToSpeech:\ " .  (($time_end - $time_start)) );


        $ret['localFile'] = $this->convertFileToAsterisk($ret['transcript'], $ret['fileName']);

        //if exist parameters on DialogFlow, set it to Asterisk
        if($astrid_answer['parameters'])
            foreach ($astrid_answer['parameters'] as $key => $val) {

                echo "\n";
                $this->agi->set_variable($key, $val);

            }

        echo "\n";

        $this->agi->set_variable("resposta", $ret['localFile'] );

        return 1;

    }

    /**
     * This function control the yesno case
     *
     * @param string $contextName
     * @return int|mixed
     */
    public function yesNo($contextName = '') {
	    $yesno = 0;

	    echo "\n";
        $this->agi->set_variable("not_understand", 0 );
	    echo "\n";
	    $this->agi->set_variable("returnToAsterisk", "");	
	    echo "\n";

        $this->agi->exec("NOOP", "yesNo\ ");

        $time_start = microtime(true);

        //translate audio to text
        $message = $this->speechToText( $this->file_path . ".wav");

        $this->agi->exec("NOOP", "Message\ " . $message['transcript'] );

        $time_end = microtime(true);

        $this->agi->exec("NOOP", "Total\ Execution\ Time\ speechToText:\ " .  (($time_end - $time_start)) );

	    $this->agi->exec("NOOP", "Status\ " . $message['status'] );


	    $this->agi->exec("NOOP", "contextName\ " . $contextName );


        if($message['status'] == 1) {
		
            $this->agi->exec("NOOP", " entrei\ no\ callastrid\ ");
            
	        $time_start = microtime(true);

            //send text to astrid-api
            $astrid_answer = $this->callAstrid($message['transcript'], $contextName);

		    $this->agi->exec("NOOP", "Sai\ do\ AstridAnswer:\ ");

            $time_end = microtime(true);

	        echo "\n";

            $this->agi->exec("NOOP", "AstridAnswer:\ " . $astrid_answer['text']);

            $this->agi->exec("NOOP", "Total\ Execution\ Time\ callAstrid:\ " . (($time_end - $time_start)));

            $time_start = microtime(true);


            //to avoid null answers 
            if (!$astrid_answer['text']) {

                $this->agi->set_variable("not_understand", 1);
                return 1;

            }


            //translate text to audio
            $ret = $this->textToSpeech($astrid_answer['text']);

            $time_end = microtime(true);

            $this->agi->exec("NOOP", "Total\ Execution\ Time\ textToSpeech:\ " . (($time_end - $time_start)));


            $ret['localFile'] = $this->convertFileToAsterisk($ret['transcript'], $ret['fileName']);

            //if exist parameters on DialogFlow, set it to Asterisk
            if ($astrid_answer['parameters']) {
                $this->agi->exec("NOOP", "Entrei\ no\ if:\ ");
                
                foreach ($astrid_answer['parameters'] as $key => $val) {
                    
                    $this->agi->exec("NOOP", "key:\ " . $key);
                    $this->agi->exec("NOOP", "Val:\ " . $val);

                    echo "\n";
                    $this->agi->set_variable($key, $val);
		
        		    if($key == "returnToAsterisk")
            			$yesno = 1;


                }
            }

            echo "\n";

            $this->agi->set_variable("resposta", $ret['localFile']);
	
	        echo "\n";

        } else {

            echo "\n";
            $this->agi->set_variable("not_understand", 1 );

        }

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

            $this->agi->set_variable("not_understand", 1 );
            return 1;

        }

        echo "\n";

        $this->agi->set_variable("resposta", $ret);

        return 1;

    }

    /**
     * This function will call the Astrid API to get the Dialogflow answer
     *
     * @param $message
     * @param string contextName
     * @return array
     */
    public function callAstrid($message, $contextName = "")
    {

        $this->logger->info($this->file_path.' CALL DIALOG FLOW');
        //($projectId, $text, $sessionId, $languageCode = 'pt-BR')
        $ret = Dialogflow::detectIntentTexts('voicebot-judite',$message, $this->file_name, 'pt-BR', $contextName);

        $this->logger->info($this->file_path.' DIALOG FLOW ANSWER:'. print_r($ret, true));

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

        $translate = new Translate();
        $response = $translate->TranslateTextToSpeech($message);
	
	    
        $ret['transcript'] = "/var/lib/asterisk/agi-bin/asterisk-api/audios/" . $response;
        $ret['fileName'] =  $response;
        $ret['status'] = 1;
	
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
    public function textToSpeechName($messageText, $messageName)
    {

        $translate = new Translate();
        $response = $translate->TranslateTextToSpeechName($messageText, $messageName);
	
	    
        $ret['transcript'] = "/var/lib/asterisk/agi-bin/asterisk-api/audios/" . $response;
        $ret['fileName'] =  $response;
        $ret['status'] = 1;
	
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

        // $this->curl->setOpt("CURLOPT_POSTFIELDS",true);
        // $this->curl->setHeader('Content-Type', 'multipart/form-data');


	    $this->agi->exec("NOOP", "audioPath\ " . $audio_path );

        $translate = new Translate();
        $response = $translate->TranslateSpeechToText($audio_path);
                
        $ret = [];
        $ret = $response;
        
        $ret['status'] = 1;

        $this->agi->exec("NOOP", "confidence\ " . $ret['confidence'] );
        $this->agi->exec("NOOP", "transcript\ " . $ret['transcript'] );

        $this->logger->info($this->file_path.' CONFIDENCE:'.$ret['confidence']. "-" .$ret['transcript']);

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

        //if(!stream_resolve_include_path("/tmp/" . $FileNameWithOutExt . ".wav" )){
	  if (!system( "[ -e '"."/tmp/" . $FileNameWithOutExt . ".wav"."' ] && echo 1 || echo 0 ")){
         
            $audio = file_get_contents($s3Url);

            //save file on /tmp
            file_put_contents("/tmp/" . $fileName, $audio);

            system("lame --decode /tmp/$fileName - | sox -v 0.5 -t wav - -t wav -b 16 -r 8000 -c 1 /tmp/$FileNameWithOutExt.wav");
        }

        return "/tmp/" . $FileNameWithOutExt;

    }
}
