<?php
namespace Enupal\YaTranslate;


/**
 * Yandex Translate
 * This system is like Bing Translator or Google translate,
 * but api is free for use and without strict limits.
 *
 * API offers text translation features for over 30 languages.
 *
 * To begin using this class you need to get your own API key, you can do it just in pair of clicks.
 * Firstly create a Yandex account - https://passport.yandex.com/passport
 * Finally get your API key - https://tech.yandex.com/key/form.xml?service=trnsl
 *
 * Official api description and documentation:
 * in English - https://tech.yandex.com/translate/doc/dg/concepts/api-overview-docpage/
 * in russian - https://tech.yandex.ru/translate/doc/dg/concepts/api-overview-docpage/
 *
 * @author  Tebiev Aleksandr <tebiev@mail.com>
 * @link    https://github.com/beeyev/yandex-translate
 **/
class Translate
{

    /**
     * The API base URL.
     */
    const   API_URL = 'https://translate.yandex.net/api/v1.5/tr.json/';

    /**
    * Yandex API Key.
    *
    * @var string
    */
    private $_apiKey;

    /**
    * Default constructor.
    *
    * @param string $apiKey Yandex API Key
    *
    * @return self
    */
    function __construct($apiKey = null)
    {
        if ($apiKey) {
            $this->setApiKey($apiKey);
        }
        return $this;
    }

    /**
    * API key Setter
    *
    * @param string $apiKey Yandex API Key
    *
    * @throws TranslateException if the API key is not provided
    *
    * @return self
    */
    public function setApiKey($apiKey)
    {
        if ($apiKey) {
            $this->_apiKey = $apiKey;
        } else {
            throw new TranslateException('Error: setApiKey() - Api key is required');
        }

        return $this;
    }


    /**
    * API Key Getter
    *
    * @throws TranslateException if the API key was not set
    *
    * @return string
    */
    public function getApiKey()
    {
        if (!$this->_apiKey) {
            throw new TranslateException('Error: makeCall() - API key was not set');
        }

        return $this->_apiKey;
    }

    /**
    * Gets a list of translation directions supported by the service.
    *
    * @link https://tech.yandex.com/translate/doc/dg/reference/getLangs-docpage/
    *
    * @param string $detailsLang If set, the response contains explanations of language codes. Language names are output in the language corresponding to the code in this parameter.
    *
    * @return array
    */
    public function getPossibleTranslations($detailsLang = null)
    {
        return $this->makeCall(
            'getLangs', array(
                'ui' => $detailsLang
            )
        );
    }


    /**
    * Detects the language of the specified text.
    *
    * @link https://tech.yandex.com/translate/doc/dg/reference/detect-docpage/
    *
    * @param string $text The text to detect the language for.
    * @param string $hint Optional parameter. List of the most possible languages, separator is comma.
    *
    * @return string
    */
    public function detectLanguage($text, $hint = null)
    {
        if (is_array($hint)) {
            $hint = implode(',', $hint);
        }

        $callResult = $this->makeCall(
            'detect', array(
                'text' => $text,
                'hint' => $hint
            )
        );

        return $callResult['lang'];
    }

    /**
    * Translates text to the specified language.
    *
    * @link https://tech.yandex.com/translate/doc/dg/reference/translate-docpage/
    *
    * @param string|array           $text       The text to translate.
    * @param string                 $language   The translation direction.
    * @param "plain"|"html"|"auto"  $format     If input text contains html code
    * @param int                    $options    Read more on the website
    *
    * @return object
    */
    public function translate($text, $language, $format = 'plain', $options = null)
    {

        /*
            html code autodetection
            i will appreciate if smb tell me a beetter way
            to detect html code in a string
        */
        if ($format == 'auto') {
            if (is_array($text)) {
                $textD = implode('', $text);
            } else {
                $textD = $text;
            }
            $format = $textD == strip_tags($textD) ? 'plain' : 'html';
        }

        $callResult = $this->makeCall(
            'translate', array(
                'text'      => $text,
                'lang'      => $language,
                'format'    => $format,
            )
        );
        return new TranslationResponse($callResult, $text);
    }

    /**
    * Makes call to an API server
    *
    * @param string $uri                API method
    * @param array  $requestParameters  API parameters
    *
    * @throws TranslateException When curl error has occurred
    * @throws TranslateException When curl error has occurred
    * @throws TranslateException When API server returns error
    *
    * @return array
    */
    protected function makeCall($uri, array $requestParameters)
    {
        $requestParameters['key'] = $this->getApiKey();

        $text = '';
        if (isset($requestParameters['text']) && is_array($requestParameters['text'])) {
              $text = '&text=' . implode('&text=', $requestParameters['text']);
              unset($requestParameters['text']);
        }

        $requestParameters = http_build_query($requestParameters) . $text;

        $curlOptions = array(
                CURLOPT_URL             => self::API_URL . $uri,
                CURLOPT_POSTFIELDS      => $requestParameters,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CONNECTTIMEOUT  => 20,
                CURLOPT_TIMEOUT         => 60,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_CUSTOMREQUEST   => 'POST',
            );

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        $callResult = curl_exec($ch);

        if (!$callResult) {
            throw new TranslateException('Error: makeCall() - cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        $callResult = json_decode($callResult, true);

        if (isset($callResult['code']) && $callResult['code'] > 200) {
            throw new TranslateException('API error: ' .$callResult['message'], $callResult['code']);
        }

        return $callResult;
    }
}
