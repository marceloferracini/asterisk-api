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

    function TranslateSpeechToText($audio_path)
    {
        $fields = array('file' => '@' . $audio_path);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://storage.googleapis.com/upload/storage/v1/b/bk-audios/o?uploadType=media&name=Deus-Comando',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $fields,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: audio/vnd.wave',
            'Authorization: Bearer ya29.a0ARrdaM_s5DSPfVbQkNK42GxTGgrACDNKTtA72enxw0Ue8CqoBHKIRN9gcifkqoT7IPYm1G7sIOmn6bnFp8sBC8CONUjjiORQ481wjADFibaH1iI0AkpDG8ek9_7yjRzXgToiXbTeqrERTwwtMD7l8DkLdhBP'
        ),
        ));

        $responseStorage = curl_exec($curl);

        curl_close($curl);
        return $responseStorage;

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
        //         "uri": "gs://bk-audios/obg-Deus"
        //     }
        // }',
        // CURLOPT_HTTPHEADER => array(
        //     'Content-Type: application/json',
        //     'Authorization: Bearer ya29.a0ARrdaM_s20z0u8UmNk5wUCDc8P3O-xJPiluQbVUgp5TMqqvqWCKGNyGVCTFWVy2jbCDVXNf_IUJOrPIy3NkvRNTqDLKJAMVHjm-QQT8AlCVnZLivq_21zItNS6bgyF5T1xf7tC2kimflo2fSDutPXvSpuyIA'
        // ),
        // ));

        // $response = curl_exec($curl);

        // curl_close($curl);

        // return $response;




    }
    


}
