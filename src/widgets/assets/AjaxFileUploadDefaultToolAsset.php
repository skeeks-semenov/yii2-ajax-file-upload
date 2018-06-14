<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 27.04.2017
 */

namespace skeeks\yii2\ajaxfileupload\widgets\assets;

/**
 * Class AjaxFileUploadDefaultToolAsset
 * @package skeeks\yii2\ajaxfileupload\widgets\assets
 */
class AjaxFileUploadDefaultToolAsset extends AjaxFileUploadWidgetAsset
{
    public $css = [];

    public $js = [
        'js/tools/tool-default-upload.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'dosamigos\fileupload\FileUploadPlusAsset',
        'skeeks\yii2\ajaxfileupload\widgets\assets\AjaxFileUploadWidgetAsset',
    ];
}