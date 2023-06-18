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
class AjaxFileUploadBigToolAsset extends AjaxFileUploadWidgetAsset
{
    public $css = [];

    public $js = [
        'js/tools/tool-big-upload.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'skeeks\yii2\ajaxfileupload\widgets\assets\AjaxFileUploadWidgetAsset',
    ];
}