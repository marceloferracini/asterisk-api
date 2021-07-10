<?php

require __DIR__ . '/../vendor/autoload.php';

use \Curl\Curl;

// Imports the Google Cloud client library
use Google\Cloud\Speech\SpeechClient;

class Translate 
{
    private $dotenv;

    public function  __construct($array_file)
    {
        //load .env file
        $this->dotenv = new Dotenv\Dotenv(__DIR__ . "/../");
        $this->dotenv->load();
    }

    public function TranslateTextToSpeech($message) {        

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://texttospeech.googleapis.com/v1beta1/text:synthesize?key=AIzaSyCYBsO7GhOFMmxei8Bo0YyrGlstiqJfZok',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "input": {
                "text" : "'.$message.'"
            },
            "voice" : {
                "languageCode" : "pt-BR",
                "name" : "pt-BR-Standard-A"
            },
            "audioConfig" : {
                "audioEncoding" : "MP3"
            }
        }',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $fileData = json_decode($response, true);
        
        $result = [];
        $result['AudioStream'] = base64_decode($fileData['audioContent']);
        $result['file_name'] = uniqid().'-Google.mp3';


	    file_put_contents("/var/lib/asterisk/agi-bin/asterisk-api/audios/" . $result['file_name'], $result['AudioStream']);


        return $result['file_name'];

    }

    function TranslateSpeechToText($audio_path)
    {

        // Instantiates a client
        $speech = new SpeechClient([
            'projectId' => 'voicebot-judite',
            'languageCode' => 'pt-BR',
        ]);

        $options = [
            'encoding' => 'LINEAR16',
            'sampleRateHertz' => 8000,
        ];

        // Detects speech in the audio file
        $results = $speech->recognize(fopen($audio_path, 'r'), $options);

        foreach ($results as $result) {
            $ret =  array(
                             'transcript' => $result->alternatives()[0]['transcript'],
                             'confidence' => $result->alternatives()[0]['confidence']
                         );
        }

        return $ret;

    }
    


}
