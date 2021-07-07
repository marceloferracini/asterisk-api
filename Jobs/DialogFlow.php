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
       // $key = array('credentials' => __DIR__ . '/../Storage/google_key.json');
	    $key = array('credentials' => '/var/lib/asterisk/agi-bin/asterisk-api/Storage/google_key.json');

        $logger->info('set credentials ');

	    $sessionsClient = new SessionsClient($key);

        $logger->info('new sessionsClient');

        $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());

        $logger->info('get session ');

        //printf('Session path: %s' . PHP_EOL, $session);

        // create text input
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($languageCode);


	    $logger->info('create text input ');
        
        if($contextName){

            $logger->info(' entrei no contextname ');

            try{

            putenv('GOOGLE_APPLICATION_CREDENTIALS=/var/lib/asterisk/agi-bin/interface-astrid-asterisk/Storage/google_key.json');
            //$client->useApplicationDefaultCredentials();

                    $contextsClient = new ContextsClient();

                    //context
                    $context[] = new Context();
                    $formattedName = $contextsClient->contextName($projectId, $sessionId, $contextName);
                    $context[0]->setName($formattedName);

            $logger->info(' context criado ');

                    //"projects/voicebot-judite/agent/sessions/$sessionId/contexts/decisao"
                    $context[0]->setLifespanCount(2);

                    //Query Parameters
                    $queryParameters['queryParams'] = new QueryParameters();
                    $queryParameters['queryParams']->setContexts($context);

            } catch (Exception $e) {
                    //echo 'Exceção capturada: ',  $e->getMessage(), "\n";
                $logger->info('erro no contexto: '. $e->getMessage());
            }

        }else{

	        $logger->info('n entrei no contextname ');

            $queryParameters = array();

        }

        $logger->info('contextName ok');

        // create query input
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);


        // get response and relevant info
        $response = $sessionsClient->detectIntent($session, $queryInput, $queryParameters);

        $logger->info('get response ');

        $queryResult = $response->getQueryResult();

        $queryText = $queryResult->getQueryText();
        $intent = $queryResult->getIntent();
        $ret['intentDisplayName'] = $intent->getDisplayName();
        $ret['confidence'] = $queryResult->getIntentDetectionConfidence();
        //$fulfilmentText = $queryResult->getFulfillmentText();

        $allResponses = $queryResult->getFulfillmentMessages();

        $logger->info('get all responses ');

        $ret['parameters']  = json_decode($queryResult->getParameters()->serializeToJsonString());

        $iterator = $allResponses->getIterator();

        while($iterator->valid()) {

            //echo $iterator->key() . ' => ' . print_r(get_class_methods($iterator->current() ), true) . "\n";

            //first response
            if($iterator->key() == 0) {

                $content = $iterator->current()->getPayload();

                if ($content) {

                    $json = json_decode($content->serializeToJsonString());
                    $ret['text'] = $json->speech->text;

                }
            }

            $iterator->next();
        }
        
        $sessionsClient->close();

	    $logger->info('return: '.print_r($ret,true));
        return $ret;
    }

}
