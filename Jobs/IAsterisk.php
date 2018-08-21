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


    public function ConvertFileToAsterisk($s3Url, $fileName);

}