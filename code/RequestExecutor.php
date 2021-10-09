<?php

    namespace App\trial\code;

    use newgenvision\code\exceptions\RequestExecutionException;
    use newgenvision\code\interfaces\IRequestExecution;

    final class RequestExecutor implements IRequestExecution
    {
        private array $requestParams;
        private string $requestUrl;

        public function setOptions(array $requestParams, string $requestUrl) : self
        {
            $this->requestParams = $requestParams;
            $this->requestUrl = $requestUrl;
            return $this;
        }

        /**
         * @return string json as result of request
         */
        public function executeRequest(): string
        {
            $formData = http_build_query($this->requestParams);
            $options = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $formData,
            ];

            $ch = curl_init($this->requestUrl);
            curl_setopt_array($ch, $options);
            $json = curl_exec($ch);
            curl_close($ch);

            if (false === $json) {
                throw new RequestExecutionException('Failed to execution');
            }
            return $json;
        }
    }
