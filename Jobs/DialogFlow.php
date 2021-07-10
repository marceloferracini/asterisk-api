<?php

require __DIR__.'/../vendor/autoload.php';

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\QueryParameters;
use Google\Cloud\Dialogflow\V2\Context;
use Google\Cloud\Dialogflow\V2\ContextsClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

abstract class DialogFlow
{

    /**
     * This function get the intection on DialogFlow and return the text on Response > speech > text
     *
     * @param $projectId
     * @param $text
     * @param $sessionId
     * @param string $languageCode
     * @param string $contextName
     *
     * @return mixed
     *
     * @throws \Google\ApiCore\ApiException
     * @throws \Google\ApiCore\ValidationException
     */

    public static function detectIntentTexts($projectId, $text, $sessionId, $languageCode = 'pt-BR', $contextName = '')
    {
        //logger
        $logger = new Logger('AsteriskLogger');
        $logger->pushHandler(new StreamHandler('/tmp/Asterisk.log', Logger::DEBUG));

        $logger->info('Entrei no detectIntentTexts ');

        // new session
	    $key = array('credentials' => '/var/lib/asterisk/agi-bin/asterisk-api/Storage/google_key.json');

        $logger->info('set credentials ');

	    $sessionsClient = new SessionsClient($key);

        $logger->info('new sessionsClient');

        $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());

        $logger->info('get session ');

        // create text input
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($languageCode);

	    $logger->info('create text input ');

        $logger->info('contextName ok');

        // create query input
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);


        // get response and relevant info
        $response = $sessionsClient->detectIntent($session, $queryInput);

        $logger->info('get response ');

        $queryResult = $response->getQueryResult();

        $queryText = $queryResult->getQueryText();
        $intent = $queryResult->getIntent();
        $ret['intentDisplayName'] = $intent->getDisplayName();
        $ret['confidence'] = $queryResult->getIntentDetectionConfidence();

        $allResponses = $queryResult->getFulfillmentMessages();

        $logger->info('get all responses ');

        $ret['parameters']  = json_decode($queryResult->getParameters()->serializeToJsonString());

        $iterator = $allResponses->getIterator();

        while($iterator->valid()) {

            //first response
            if($iterator->key() == 0) {

                $content = $iterator->current()->getText();

                if ($content) {

                    $json = json_decode($content->serializeToJsonString());
                    $ret['text'] = $json->text[0];

                }
            }

            $iterator->next();
        }
        
        $sessionsClient->close();

	    $logger->info('return: '.print_r($ret,true));
        return $ret;
    }

}
