# Yandex Translate

Yandex API offers text translation features for over 30 languages. 


API reference in [English](https://tech.yandex.com/translate/doc/dg/concepts/api-overview-docpage/)

## Requirements

- PHP 5.3 or higher
- cURL

## Install

Via Composer

``` bash
$ composer require enupal/yandex-translate
```

## Usage

``` php
use Enupal\YaTranslate\Translate;

try {
    $tr = new Translate('yourApiKey');
    $result = $tr->translate("Hey baby, what are you doing tonight?", 'fr');
    
    echo $result;                           // Hey bébé, tu fais quoi ce soir?
    echo $result->sourceText();             // Hey baby, what are you doing tonight?
    echo $result->translationDirection();   // en-fr
    
    var_dump($result->translation());       // array (size=1)
                                            // 0 => string 'Hey bébé, tu fais quoi ce soir?'
} catch (\Enupal\YaTranslate\TranslateException $e) {
    //Handle exception
}
```