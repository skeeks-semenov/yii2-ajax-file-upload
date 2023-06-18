<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 26.04.2017
 */

namespace skeeks\yii2\ajaxfileupload\widgets;

use skeeks\yii2\ajaxfileupload\widgets\assets\AjaxFileUploadBigToolAsset;
use skeeks\yii2\models\CmsStorageFile;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class AjaxFileUploadDefaultTool
 *
 * @package skeeks\yii2\ajaxfileupload\widgets
 */
class AjaxFileUploadBigTool extends AjaxFileUploadTool
{
    /**
     * @var array
     */
    public $options = [];

    /**
     * @var array
     */
    public $clientOptions = [];

    /**
     * Размер одной порции при загрузке файлов
     * @var float|int
     */
    public $portion = 1048576 * 1;

    /**
     * Дозагрузка больших файлов
     * @var float|int
     */
    public $additional_loading = 1048576 * 50;

    /**
     * Время ожидания соединения с сервером для загрузки порции
     * @var int
     */
    public $timeout = 15000;

    /**
     * @var bool
     */
    public $isDragAndDrop = true;

    /**
     * По умолчанию будет задана зона виджета
     * Можно укзать body или .custom-selector-class иди #custom-selector-id
     * @var null
     */
    public $dropZone = null;

    /**
     * @var array
     */
    public $defaultClientOptions = [];

    public function init()
    {
        parent::init();

        if (!$this->upload_url) {
            $this->upload_url = \yii\helpers\Url::to(['/ajaxfileupload/upload/bin']);
        }

        $this->id = $this->ajaxFileUploadWidget->id."_".$this->id;

        $this->options['id'] = $this->id;
        $this->options['multiple'] = $this->ajaxFileUploadWidget->multiple;
        $this->options['accept'] = $this->ajaxFileUploadWidget->accept;

        $this->clientOptions = ArrayHelper::merge($this->defaultClientOptions, $this->clientOptions);

        $this->clientOptions['id'] = $this->id;


        $this->clientOptions['upload_url'] = $this->upload_url;
        $this->clientOptions['additional_loading'] = $this->additional_loading;

        $this->clientOptions['portion'] = $this->portion;
        $this->clientOptions['timeout'] = $this->timeout;
        $this->clientOptions['isDragAndDrop'] = (int) $this->isDragAndDrop;

        if (!$this->dropZone) {
            $this->clientOptions['dropZone'] = "#{$this->ajaxFileUploadWidget->id}";
        } else {
            $this->clientOptions['dropZone'] = $this->dropZone;
        }

        /*if (!$this->ajaxFileUploadWidget->multiple) {
            $this->clientOptions['uploadfile']['singleFileUploads'] = true;
        }*/
    }

    public function run()
    {
        AjaxFileUploadBigToolAsset::register($this->ajaxFileUploadWidget->view);

        $js = Json::encode($this->clientOptions);
        $this->ajaxFileUploadWidget->view->registerJs(<<<JS
(function(sx, $, _)
{
    new sx.classes.fileupload.tools.BigUploadTool(sx.{$this->ajaxFileUploadWidget->id}, {$js});
})(sx, sx.$, sx._);
JS
        );
        return Html::fileInput($this->id, '', $this->options);
    }


}