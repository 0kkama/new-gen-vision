<?php

    namespace newgenvision\code\exceptions;

    use Exception;

    class ApiResponseException extends Exception
    {
        private array $response;

        public function __construct(string $message, array $response)
        {
            $this->response = $response;
            parent::__construct($message);
        }

        public function getResponse() : array
        {
            return $this->response;
        }
        // ...
    }
