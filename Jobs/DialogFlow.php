<?php

require __DIR__.'/../vendor/autoload.php';

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\QueryParameters;
use Google\Cloud\Dialogflow\V2\Context;
use Google\Cloud\Dialogflow\V2\ContextsClient;



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

        // new session
       // $key = array('credentials' => __DIR__ . '/../Storage/google_key.json');
	$key = array('credentials' => '/var/lib/asterisk/agi-bin/asterisk-api/Storage/google_key.json');
        
	$sessionsClient = new SessionsClient($key);
        $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());

        //printf('Session path: %s' . PHP_EOL, $session);

        // create text input
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($languageCode);

        /*
        $contextsClient = new ContextsClient();
        try {
            $formattedParent = $contextsClient->sessionName($projectId, $sessionId);
            // Iterate through all elements
            $pagedResponse = $contextsClient->listContexts($formattedParent);


            foreach ($pagedResponse->iterateAllElements() as $element) {
                //var_dump(get_class_methods($element));

               // var_dump($element->getName());
            }

            // OR iterate over pages of elements
            $pagedResponse = $contextsClient->listContexts($formattedParent);
            foreach ($pagedResponse->iteratePages() as $page) {
                foreach ($page as $element) {
                    // doSomethingWith($element);
                }
            }
        } finally {
            $contextsClient->close();
        }
        */

        if($contextName){

            $contextsClient = new ContextsClient();

            //context
            $context[] = new Context();
            $formattedName = $contextsClient->contextName($projectId, $sessionId, $contextName);
            $context[0]->setName($formattedName);

            //"projects/astrid-5a294/agent/sessions/$sessionId/contexts/decisao"
            $context[0]->setLifespanCount(1);

            //Query Parameters
            $queryParameters['queryParams'] = new QueryParameters();
            $queryParameters['queryParams']->setContexts($context);

        }else{

            $queryParameters = array();

        }


        // create query input
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);


        // get response and relevant info
        $response = $sessionsClient->detectIntent($session, $queryInput, $queryParameters);

        $queryResult = $response->getQueryResult();

        //var_dump($queryResult->getOutputContexts());
        //$queryResult->setOutputContexts($context);

        $queryText = $queryResult->getQueryText();
        $intent = $queryResult->getIntent();
        $ret['intentDisplayName'] = $intent->getDisplayName();
        $ret['confidence'] = $queryResult->getIntentDetectionConfidence();
        //$fulfilmentText = $queryResult->getFulfillmentText();

        $allResponses = $queryResult->getFulfillmentMessages();

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
