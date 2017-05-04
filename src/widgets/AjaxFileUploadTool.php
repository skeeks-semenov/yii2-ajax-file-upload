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
 * Download Tool
 *
 * @property $name;
 * @property $icon;
 * @property $image;
 *
 * Class AjaxFileUploadTool
 * @package skeeks\yii2\ajaxfileupload\widgets
 */
abstract class AjaxFileUploadTool extends Widget
{
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





    /**
     * @var string
     */
    protected $_name = '';

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }


    /**
     * @var string
     */
    protected $_icon = '';

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->_icon;
    }

    /**
     * @param $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->_icon = $icon;
        return $this;
    }


    /**
     * @var string
     */
    protected $_image = '';

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->_image;
    }

    /**
     * @param $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->_image = $image;
        return $this;
    }
}