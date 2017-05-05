<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 26.04.2017
 */
namespace skeeks\yii2\ajaxfileupload;
/**
 * Class FileUploadModule
 *
 * @package skeeks\yii2\ajaxfileupload
 */
class AjaxFileUploadModule extends \yii\base\Module
{
    public $controllerNamespace = 'skeeks\yii2\ajaxfileupload\controllers';

    public $private_tmp_dir     = '@runtime/ajaxfileupload';

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
            \Yii::$app->i18n->translations['skeeks/yii2-ajaxfileupload'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath' => '@skeeks/yii2/ajaxfileupload/messages',
                'fileMap' => [
                    'skeeks/yii2-ajaxfileupload' => 'main.php',
                ],
                //'on missingTranslation' => \Yii::$app->i18n->missingTranslationHandler
            ];
            self::$isRegisteredTranslations = true;
        }
    }
}
