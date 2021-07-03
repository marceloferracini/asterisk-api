<?php


class Translate {

    public function TranslateTextToSpeech($message) {

        // creating AGI object
        $agi = new AGI();

        //$file_url = "";
        $agi->exec("NOOP", "CAIU AQUI DENTRO PELO MENOS ");

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