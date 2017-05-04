<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 27.04.2017
 */
namespace skeeks\yii2\fileupload\widgets\assets;
use dosamigos\fileupload\FileUpload;
use dosamigos\fileupload\FileUploadAsset;
use dosamigos\fileupload\FileUploadPlusAsset;
use skeeks\yii2\base\AssetBundle;
use skeeks\yii2\models\CmsStorageFile;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class AjaxFileUploadWidgetAsset
 *
 * @package skeeks\yii2\fileupload\widgets\assets
 */
class AjaxFileUploadWidgetAsset extends AssetBundle
{
    public $sourcePath = '@skeeks/cms/fileupload/widgets/assets/src';

    public $css = [
        'css/ajax-file-upload.css'
    ];

    public $js = [
        'js/ajax-file-upload.js',
        'js/ajax-file-upload-tool.js',
        'js/ajax-file-upload-file.js',
        'js/tools/tool-remote-upload.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'skeeks\sx\assets\Core',
    ];
}