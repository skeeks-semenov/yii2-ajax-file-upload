<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 26.04.2017
 */

namespace skeeks\yii2\ajaxfileupload\widgets;

use skeeks\yii2\ajaxfileupload\widgets\assets\AjaxFileUploadDefaultToolAsset;
use skeeks\yii2\models\CmsStorageFile;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 *
 * 'options' => [
 * 'accept' => 'image/*',
 * 'multiple' => true
 * ],
 *
 *
 *  'clientOptions' => [
 *      'uploadfile' => [
 * //'maxFileSize' => 2000000,
 * //'minFileSize' => 500,
 * 'acceptFileTypes' => new \yii\web\JsExpression('/(\.|\/)(gif|jpe?g|png)$/i'),
 * 'disableImageResize' => '/Android(?!.*Chrome)|Opera/.test(window.navigator && navigator.userAgent)',
 * 'imageMaxWidth' => 10000,
 * 'imageMaxHeight' => 10000,
 * 'loadImageMaxFileSize' => 100000000, // 100MB
 * 'imageCrop' => true,
 *
 * 'previewMaxWidth' => 400,
 * 'previewMaxHeight' => 300,
 * 'previewCrop' => true,
 * 'limitMultiFileUploads' => 1,
 * 'limitConcurrentUploads' => 1,
 * 'dropZone' => new \yii\web\JsExpression('$(\'body\')')
 * ],
 * ],
 *
 *
 * Class AjaxFileUploadDefaultTool
 *
 * @package skeeks\yii2\ajaxfileupload\widgets
 */
class AjaxFileUploadDefaultTool extends AjaxFileUploadTool
{
    public $options = [];
    public $clientOptions = [];
    public $defaultClientOptions = [
        'uploadfile' =>
            [
                'disableImageResize' => '/Android(?!.*Chrome)|Opera/.test(window.navigator && navigator.userAgent)',
                /*'imageCrop' => true,*/

                'previewMaxWidth'        => 150,
                'previewMaxHeight'       => 150,
                /*'imageMaxHeight' => 300,
                'imageMaxWidth' => 300,*/
                'previewCrop'            => false,
                'previewCanvas'          => false,
                'previewThumbnail'       => false,
                'limitMultiFileUploads'  => 1,
                'limitConcurrentUploads' => 1,
            ],
    ];

    public function init()
    {
        parent::init();

        $this->id = $this->ajaxFileUploadWidget->id."_".$this->id;

        $this->options['id'] = $this->id;
        $this->options['data-url'] = $this->upload_url;
        $this->options['multiple'] = $this->ajaxFileUploadWidget->multiple;
        $this->options['accept'] = $this->ajaxFileUploadWidget->accept;

        $this->clientOptions = ArrayHelper::merge($this->defaultClientOptions, $this->clientOptions);
        $this->clientOptions['id'] = $this->id;
        $this->clientOptions['uploadfile']['dropZone'] = new \yii\web\JsExpression("$('#{$this->ajaxFileUploadWidget->id}')");

        if (!$this->ajaxFileUploadWidget->multiple) {
            $this->clientOptions['uploadfile']['singleFileUploads'] = true;
        }
    }

    public function run()
    {
        AjaxFileUploadDefaultToolAsset::register($this->ajaxFileUploadWidget->view);

        $js = Json::encode($this->clientOptions);
        $this->ajaxFileUploadWidget->view->registerJs(<<<JS
(function(sx, $, _)
{
    new sx.classes.fileupload.tools.DefaultUploadTool(sx.{$this->ajaxFileUploadWidget->id}, {$js});
})(sx, sx.$, sx._);
JS
        );
        return Html::fileInput($this->id, '', $this->options);
    }


}