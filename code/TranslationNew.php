<?php

    namespace App\trial\code;

    use newgenvision\code\exceptions\ApiResponseException;
    use newgenvision\code\exceptions\InvalidConfigException;
    use newgenvision\code\exceptions\InvalidLanguageException;
    use newgenvision\code\exceptions\JsonDecodeException;
    use newgenvision\code\exceptions\TextLengthException;
    use newgenvision\code\exceptions\UnknownFormatException;
    use newgenvision\code\interfaces\IRequestExecution;

    final class TranslationNew
    {
        private const TRANSLATE_YA_URL = 'https://translate.yandex.net/api/v1.5/tr.json/translate';
        private const HTTP_CODE_OK = 200;
        private const MAX_TEXT_LENGTH = 10000;
        private const LANG_PATTERN = '~^[a-z]{2,3}-[a-z]{2,3}$~';

        /**
         * @throws InvalidConfigException
         */
        public function __construct(private string $key, private IRequestExecution $executor)
        {
            if (empty($this->key)) {
                throw new InvalidConfigException('Field $key is required');
            }
        }

        /**
         * @param string $format text format need to translate
         * @param string $text text for translation
         * @param string $lang direction of translation
         * @return string result of translation
         * @throws \JsonException|ApiResponseException|JsonDecodeException
         */
        public function translateText(string $format, string $text, string $lang): string
        {
            $requestParams = $this->prepareQuery($format, $text, $lang);
            $json = $this->executor->setOptions($requestParams, self::TRANSLATE_YA_URL)->executeRequest();
            $responseData = $this->getResultData($json);

            if ($responseData['code'] !== self::HTTP_CODE_OK) {
                throw new ApiResponseException('Failed to translate. Response code: ' . $responseData['code'], $responseData);
            }
            return $responseData['text'][0];
        }

        /**
         * Prepares the request for execution
         * @param string $format
         * @param string $text
         * @param string $lang
         * @return array
         * @throws InvalidLanguageException|TextLengthException|UnknownFormatException
         */
        private function prepareQuery(string $format, string $text, string $lang): array
        {
            $this->checkTextParams($text, $lang);

            $getParams = filter_var_array(['text' => $text, 'lang' => $lang], FILTER_SANITIZE_STRING);
            $format = $this->chooseFormat($format);

            $valuesForTranslationQuery = [
                'key'    => $this->key,
                'format' => $format,
            ];

            return array_merge($getParams, $valuesForTranslationQuery);
        }

        /**
         * Return response with data
         * @param string $json
         * @return array
         * @throws \JsonException|JsonDecodeException
         */
        private function getResultData(string $json): array
        {
            $responseData = json_decode($json, true, 256, JSON_THROW_ON_ERROR);

            if(empty($responseData)) {
                throw new JsonDecodeException('Failed to decode json', $json);
            }
            return $responseData;
        }

        /**
         * @param string $format
         * @return string
         * @throws UnknownFormatException
         */
        private function chooseFormat(string $format): string
        {
            return match ($format) {
                'text' => 'plain',
                'html' => 'html',
                'otherformat' => 'othervalue',
                'anotherformat' => 'anothervalue',
                default => throw new UnknownFormatException('Unknown format received: ' . $format),
            };
        }

        /**
         * Performs basic parameter checking
         * @param string $text
         * @param string $lang
         * @throws TextLengthException|InvalidLanguageException
         */
        private function checkTextParams(string $text, string $lang): void
        {
            if (mb_strlen($text) > self::MAX_TEXT_LENGTH) {
                throw new TextLengthException('The length of the text exceeds 10000 characters');
            }

            if (!preg_match(self::LANG_PATTERN, $lang)) {
                throw new InvalidLanguageException('Incorrect lang: ' . $lang);
            }
        }
    }
