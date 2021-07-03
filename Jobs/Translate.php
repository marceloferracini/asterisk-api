<?php

use \Curl\Curl;

require __DIR__ . '/../vendor/autoload.php';

class Translate 
{

    public function TranslateTextToSpeech($message) {

        //check if this text exist on DB
        //$records = $this->iTranslationsRepository->getFileByMessage( $request->message )->toArray();

        // if(count($records) != 0){
        //     foreach ($records as $record)
        //         $file_url = $record;

        // }else{

            //call api to translate   gateway can be: ['AWS' or 'Google']
            //$translationGateway = new TranslationGateways('Google');
            //$audio = $this->googleTextToSpeech($message);

            //put the file on s3
            // $s3Service = new S3Service();
            // $file_url = $s3Service->uploadToS3($audio);

            //save on db
            // $this->iTranslationsRepository->create(array(
            //         'message' => $request->message,
            //         'file' => $file_url,
            //         'created_by' => $translationGateway->gateway)
            // );

        //}

        //return $audio;

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
                "text" : "Mensagem de teste do marcelo"
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
        echo $response;

        // $client = new GuzzleHttp\Client();
        // $requestData = [
        //     'input' =>[
        //         'text' => $text
        //     ],
        //     'voice' => [
        //         'languageCode' => 'pt-BR',
        //         'name' => 'pt-BR-Standard-A'
        //     ],
        //     'audioConfig' => [
        //         'audioEncoding' => 'MP3',
        //         'pitch' => 0.00,
        //         'speakingRate' => 1.00
        //     ]
        // ];

        // try {
        //     $response = $client->request('POST', 'https://texttospeech.googleapis.com/v1beta1/text:synthesize?key=' . $googleAPIKey, [
        //         'json' => $requestData
        //     ]);
        // } catch (Exception $e) {
        //     die('Something went wrong: ' . $e->getMessage());
        // }

        // $fileData = json_decode($response->getBody()->getContents(), true);

        // $result['AudioStream'] = base64_decode($fileData['audioContent']);
        // $result['file_name'] = uniqid().'-Google.mp3';

        var_dump($curl->response);

        return $curl->response;
    }

    // function googleTextToSpeech($text)
    // {

    //     $googleAPIKey = env('GOOGLE_API_KEY');

    //     $client = new GuzzleHttp\Client();
    //     $requestData = [
    //         'input' =>[
    //             'text' => $text
    //         ],
    //         'voice' => [
    //             'languageCode' => 'pt-BR',
    //             'name' => 'pt-BR-Standard-A'
    //         ],
    //         'audioConfig' => [
    //             'audioEncoding' => 'MP3',
    //             'pitch' => 0.00,
    //             'speakingRate' => 1.00
    //         ]
    //     ];

    //     try {
    //         $response = $client->request('POST', 'https://texttospeech.googleapis.com/v1beta1/text:synthesize?key=' . $googleAPIKey, [
    //             'json' => $requestData
    //         ]);
    //     } catch (Exception $e) {
    //         die('Something went wrong: ' . $e->getMessage());
    //     }

    //     $fileData = json_decode($response->getBody()->getContents(), true);

    //     $result['AudioStream'] = base64_decode($fileData['audioContent']);
    //     $result['file_name'] = uniqid().'-Google.mp3';

    //     return $result;

    // }
    


}