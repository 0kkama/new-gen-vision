<?php

    namespace newgenvision\code\interfaces;

    interface IRequestExecution
    {
        public function setOptions(array $requestParams, string $requestUrl);
        public function executeRequest();
    }
