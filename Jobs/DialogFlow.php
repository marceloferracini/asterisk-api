<?php

require __DIR__.'/../vendor/autoload.php';

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;


abstract class DialogFlow
{

    /**
     * This function get the intection on DialogFlow and return the text on Response > speech > text
     *
     * @param $projectId
     * @param $text
     * @param $sessionId
     * @param string $languageCode
     *
     * @return mixed
     *
     * @throws \Google\ApiCore\ApiException
     * @throws \Google\ApiCore\ValidationException
     */

    public static function detectIntentTexts($projectId, $text, $sessionId, $languageCode = 'pt-BR')
    {
        // new session
        $key = array('credentials' => 'Storage/google_key.json');
        $sessionsClient = new SessionsClient($key);
        $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());
        printf('Session path: %s' . PHP_EOL, $session);

        // create text input
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($languageCode);

        // create query input
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        // get response and relevant info
        $response = $sessionsClient->detectIntent($session, $queryInput);
        $queryResult = $response->getQueryResult();
        //$queryText = $queryResult->getQueryText();
        //$intent = $queryResult->getIntent();
        //$displayName = $intent->getDisplayName();
        $confidence = $queryResult->getIntentDetectionConfidence();
        $fulfilmentText = $queryResult->getFulfillmentText();

        $allResponses = $queryResult->getFulfillmentMessages();


        $iterator = $allResponses->getIterator();

        while($iterator->valid()) {

            //echo $iterator->key() . ' => ' . print_r(get_class_methods($iterator->current() ), true) . "\n";

            //first response
            if($iterator->key() == 0) {

                $content = $iterator->current()->getPayload();

                if ($content) {

                    $json = json_decode($content->serializeToJsonString());
                    $ret = $json->speech->text;

                }
            }

            $iterator->next();
        }

/*
        // output relevant info
        print(str_repeat("=", 20) . PHP_EOL);
        printf('Query text: %s' . PHP_EOL, $queryText);
        printf('Detected intent: %s (confidence: %f)' . PHP_EOL, $displayName,
            $confidence);
        print(PHP_EOL);
        printf('Fulfilment text: %s' . PHP_EOL, $fulfilmentText);
*/

        $sessionsClient->close();


        return $ret;
    }

}