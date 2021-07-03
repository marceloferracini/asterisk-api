<?php

use \Curl\Curl;

require __DIR__ . '/../vendor/autoload.php';

class Translate 
{

    public function TranslateTextToSpeech($message) {        

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://texttospeech.googleapis.com/v1beta1/text:synthesize?key=AIzaSyDdo5kxyDffKhLt475Z_F5O4bu0nNoOjLs',
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

    function TranslateSpeechToText($text)
    {

        




    }
    


}
