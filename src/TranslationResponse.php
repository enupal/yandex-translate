<?php
namespace Enupal\YaTranslate;

/**
 * Yandex Translate
 * Response class
 *
 * @author Tebiev Aleksandr <tebiev@mail.com>
 **/
class TranslationResponse
{
    /**
    * Yandex API request result
    *
    * @var string
    */
    private $_callResult;

    /**
    * Original text before translation
    *
    * @var string
    */
    private $_sourceText;

    /**
    * Default constructor.
    *
    * @param array  $callResult  Yandex API request result
    * @param string $sourceText Original text before translation
    *
    * @return void
    */
    function __construct($callResult, $sourceText)
    {
        $this->_callResult = $callResult;
        $this->_sourceText = $sourceText;
    }

    /**
    * Original text before translation
    *
    * @return string
    */
    public function sourceText()
    {
        return $this->_sourceText;
    }

    /**
    * Translation direction
    *
    * @return string
    */
    public function translationDirection()
    {
        return $this->_callResult['lang'];
    }

    /**
    * Transalted text
    *
    * @return array
    */
    public function translation()
    {
        return $this->_callResult['text'];
    }

    /**
    * Transalted text
    *
    * @return string
    */
    public function __toString()
    {
        if (isset($this->_callResult['text'][1])) {
            return implode("\n", $this->_callResult['text']);
        }
        return $this->_callResult['text'][0];
    }

}
