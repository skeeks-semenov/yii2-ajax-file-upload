<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 26.04.2017
 */

namespace skeeks\yii2\ajaxfileupload\widgets;
use dosamigos\fileupload\FileUpload;
use dosamigos\fileupload\FileUploadAsset;
use dosamigos\fileupload\FileUploadPlusAsset;
use skeeks\yii2\ajaxfileupload\AjaxFileUploadModule;
use skeeks\yii2\IHasInfo;
use skeeks\yii2\models\CmsStorageFile;
use skeeks\yii2\traits\THasInfo;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * Инструмент загрузки
 * 
 * Class AjaxFileUploadTool
 *
 * @package skeeks\yii2\ajaxfileupload\widgets
 */
abstract class AjaxFileUploadTool extends Widget
    implements IHasInfo
{
    use THasInfo;

    /**
     * @var AjaxFileUploadWidget
     */
    public $ajaxFileUploadWidget = null;

    /**
     * @var null
     */
    public $id = null;

    /**
     * @var null backend url
     */
    public $upload_url = null;

    public function init()
    {
        parent::init();

        if (!$this->ajaxFileUploadWidget || !$this->ajaxFileUploadWidget instanceof AjaxFileUploadWidget)
        {
            throw new InvalidConfigException();
        }
        
        if (!$this->upload_url)
        {
            $this->upload_url = $this->ajaxFileUploadWidget->upload_url;
        }
    }
}