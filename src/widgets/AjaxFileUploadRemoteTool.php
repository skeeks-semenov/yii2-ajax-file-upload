<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 26.04.2017
 */

namespace skeeks\yii2\ajaxfileupload\widgets;

use skeeks\yii2\ajaxfileupload\widgets\assets\AjaxFileUploadRemoteToolAsset;
use skeeks\yii2\models\CmsStorageFile;
use yii\helpers\Json;

/**
 * Class AjaxFileUploadRemoteTool
 *
 * @package skeeks\yii2\ajaxfileupload\widgets
 */
class AjaxFileUploadRemoteTool extends AjaxFileUploadTool
{
    public $options = [];
    public $clientOptions = [];

    public function init()
    {
        parent::init();

        $this->id = $this->ajaxFileUploadWidget->id."-".$this->id;
        $this->clientOptions['id'] = $this->id;
        $this->clientOptions['upload_url'] = $this->upload_url;
    }

    public function run()
    {
        AjaxFileUploadRemoteToolAsset::register($this->ajaxFileUploadWidget->view);

        $js = Json::encode($this->clientOptions);
        $this->ajaxFileUploadWidget->view->registerJs(<<<JS
(function(sx, $, _)
{
    new sx.classes.fileupload.tools.RemoteUploadTool(sx.{$this->ajaxFileUploadWidget->id}, {$js});
})(sx, sx.$, sx._);
JS
        );
        return '';
    }


}