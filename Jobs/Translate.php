<?php

require __DIR__ . '/../vendor/autoload.php';

use \Curl\Curl;

// Imports the Google Cloud client library
use Google\Cloud\Speech\SpeechClient;

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

    function TranslateSpeechToText($audio_path)
    {

        // Instantiates a client
        $speech = new SpeechClient([
            'projectId' => 'speech-project-212814',
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

        // $uploadFileMimeType = mime_content_type($audio_path);
        // $uploadFilePostKey = 'file';

        // $uploadFile = new CURLFile(
        //     $audio_path,
        //     $uploadFileMimeType,
        //     $uploadFilePostKey
        // );
    
        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://storage.googleapis.com/upload/storage/v1/b/bk-audios/o?uploadType=media&name=Deus-Obrigado',
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => '',
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 0,
        // CURLOPT_FOLLOWLOCATION => true,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => 'POST',
        // CURLOPT_POSTFIELDS => [
        //     $uploadFilePostKey => $uploadFile,
        // ],
        // CURLOPT_HTTPHEADER => array(
        //     'Content-Type: audio/vnd.wave',
        //     'Authorization: Bearer ya29.a0ARrdaM-lhdMkUowAAjG3oTx7sWEfFq5li2MNoM-2fDz1EeO4Hu2afzJAMsRSZWzPDWgGqznHiN3NXhJWdcSemSwWpsxa4Npul2r33nCbwi0nYEo84T6PdcJUrBmj0Hi7pGJvoni3-Vpqsa0jmPmu__XY_FJY'
        // ),
        // ));

        // $responseStorage = curl_exec($curl);

        // curl_close($curl);
        // //return $responseStorage;

        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://speech.googleapis.com/v1/speech:recognize',
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => '',
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 0,
        // CURLOPT_FOLLOWLOCATION => true,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => 'POST',
        // CURLOPT_POSTFIELDS =>'{
        //     "config": {
        //         "encoding": "LINEAR16",
        //         "sampleRateHertz": 8000,
        //         "languageCode": "pt-BR",
        //         "enableWordTimeOffsets": false
        //     },
        //     "audio": {
        //         "uri": "gs://bk-audios/Deus-Obrigado"
        //     }
        // }',
        // CURLOPT_HTTPHEADER => array(
        //     'Content-Type: application/json',
        //     'Authorization: Bearer ya29.a0ARrdaM_0dTmFLxbcYLN6ogqnZmD3JzqtqqtuAphjB_aTe-WG8MAKrVBu7r1A4pN8ygczZt86CBJqljpAsI6OPdOD29eZkOUNbe28W332sVbeKMZ7Y2Bhp9sOoyUDlaEYkls72f67lbkbHtDeeUgXnOv4WwF4'
        // ),
        // ));

        // $response = curl_exec($curl);

        // curl_close($curl);

        // return $response;

    }
    


}
