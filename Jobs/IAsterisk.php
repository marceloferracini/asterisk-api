<?php

interface IAsterisk
{

    /**
     * IAsterisk constructor.
     * @param $array_file
     */
    public function  __construct($array_file);

    /**
     * IAsterisk destruct.
     */
    public function __destruct();

    /**
     * Get the size of a file and return that to Asterisk (used to try identify if is a sort message or no)
     *
     * @param $file
     * @return int
     */
    public function getFileSize($file);

    /**
     * create all tables and fill it with the default value
     */
    public function setupDB();

    /**
     * set all default messages from DB
     */
    public function getDefaultMessages($like = NULL);

    /**
     * @return mixed
     */
    public function control();

    /**
     * @param $message
     * @param string $contextName
     * @return array
     */
    public function callAstrid($message);

    /**
     * @param $audio_path
     * @return mixed
     */
    public function speechToText($audio_path);

    /**
     * @param $message
     * @return mixed
     */
    public function textToSpeech($messageTextValue, $messageTextName);

    /**
     * @param string $s3Url
     * @param string $fileName
     * @return mixed
     */
    public function convertFileToAsterisk($s3Url, $fileName);

    /**
     *
     * @return string
     */
    public function extSpeechToText();

    /**
     *
     * @param string $message
     * @return string
     */
    public function extTextToSpeech($message);

    /**
     * @param string $message
     * @param string $contextName
     * @return mixed
     */
    public function callIntenction($message = 'Começar', $contextName = '');

    /**
     * This function control the yesno case
     *
     * @param string $contextName
     * @return int|mixed
     */
    public function yesNo($contextName = '');

}