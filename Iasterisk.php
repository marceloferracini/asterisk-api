<?php

interface Iasterisk
{

    /**
     * Iasterisk constructor.
     * @param $array_file
     */
    public function  __construct($array_file);

    /**
     * Iasterisk destruct.
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
    private function callAstrid($message);

    /**
     * @param $audio_path
     * @return mixed
     */
    private function speechToText($audio_path)

    /**
     * @param $message
     * @return mixed
     */
    private function textToSpeech($message)

}