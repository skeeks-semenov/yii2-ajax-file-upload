<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 26.04.2017
 */
namespace skeeks\yii2\fileupload;
/**
 * Class FileUploadModule
 *
 * @package skeeks\yii2\fileupload
 */
class FileUploadModule extends \yii\base\Module
{
    public $controllerNamespace = 'skeeks\yii2\fileupload\controllers';

    public function init()
    {
        parent::init();
        self::registerTranslations();
    }

    static public $isRegisteredTranslations = false;

    static public function registerTranslations()
    {
        if (self::$isRegisteredTranslations === false)
        {
            \Yii::$app->i18n->translations['skeeks/yii2-fileupload'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath' => '@skeeks/yii2/fileupload/messages',
                'fileMap' => [
                    'skeeks/yii2-fileupload' => 'main.php',
                ],
                //'on missingTranslation' => \Yii::$app->i18n->missingTranslationHandler
            ];
            self::$isRegisteredTranslations = true;
        }
    }
}
