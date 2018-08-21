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
     * @return mixed
     */
    public function control();

    /**
     * @param $message
     * @return mixed
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
    public function textToSpeech($message);

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

}