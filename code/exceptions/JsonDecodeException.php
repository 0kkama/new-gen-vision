<?php

    namespace newgenvision\code\exceptions;


    use Exception;

    class JsonDecodeException extends Exception
    {
        private string $json;

        public function __construct(string $message, string $json)
        {
            $this->json = $json;
            parent::__construct($message);
        }

        public function getJson(): string
        {
            return $this->json;
        }

    }
