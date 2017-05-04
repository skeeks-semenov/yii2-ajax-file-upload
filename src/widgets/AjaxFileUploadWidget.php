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
use skeeks\yii2\ajaxfileupload\widgets\assets\AjaxFileUploadWidgetAsset;
use skeeks\yii2\models\CmsStorageFile;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\InputWidget;

/**
 * @property CmsStorageFile $cmsFile
 * @property AjaxFileUploadDefaultTool|AjaxFileUploadTool $defaultTool
 *
 * Class AjaxFileUploadWidget
 * @package skeeks\yii2\widgets\fileupload
 */
class AjaxFileUploadWidget extends InputWidget
{
    public static $autoIdPrefix = 'AjaxFileUploadWidget';

    public $view_file        = 'default';

    public $upload_url      = ['/ajaxfileupload/upload'];

    public $multiple        = false;

    public $accept          = ''; //'image/*';

    /**
     * @var AjaxFileUploadDefaultTool[]
     */
    public $tools         = [

        'default' =>
        [
            'class' => AjaxFileUploadDefaultTool::class,
            'name' => 'Загрузить',
            'icon' => 'glyphicon glyphicon-download-alt',
        ],

        'remote' =>
        [
            'class' => AjaxFileUploadRemoteTool::class,
            'name' => 'Загрузить по ссылке',
            'icon' => 'glyphicon glyphicon-globe',
        ]
    ];

    public $clientOptions         = [];

    public function init()
    {
        if (!$this->hasModel())
        {
            throw new InvalidConfigException('Invalid config');
        }

        AjaxFileUploadModule::registerTranslations();

        $this->upload_url = Url::to($this->upload_url);

        $this->options['multiple'] = $this->multiple;
        $this->clientOptions['multiple'] = $this->multiple;
        $this->clientOptions['id'] = $this->id;

        $this->clientOptions['fileStates'] = [
            'queue' => \Yii::t('skeeks/yii2-ajaxfileupload', 'Queue'),
            'process' => \Yii::t('skeeks/yii2-ajaxfileupload', 'Loading'),
            'fail' => \Yii::t('skeeks/yii2-ajaxfileupload', 'Fail'),
            'success' => \Yii::t('skeeks/yii2-ajaxfileupload', 'Success'),
        ];

        if ($this->multiple)
        {
            if ($this->cmsFiles)
            {
                foreach ($this->cmsFiles as $file)
                {
                    $fileData = [
                        'name' => $file->fileName,
                        'value' => $file->id,
                        'state' => 'success',
                        'size' => $file->size,
                        'type' => $file->mime_type,
                        'src' => $file->src,
                    ];

                    if ($file->isImage())
                    {
                        $fileData['image'] = [
                            'height' => $file->image_height,
                            'width' => $file->image_width,
                        ];
                        $fileData['preview'] = Html::img($file->src);
                    }

                    $this->clientOptions['files'][] = $fileData;
                }


            }

        } else
        {
            if ($this->cmsFile)
            {
                $fileData = [
                    'name' => $this->cmsFile->fileName,
                    'value' => $this->cmsFile->id,
                    'state' => 'success',
                    'size' => $this->cmsFile->size,
                    'type' => $this->cmsFile->mime_type,
                    'src' => $this->cmsFile->src,
                ];
                if ($this->cmsFile->isImage())
                {
                    $fileData['image'] = [
                        'height' => $this->cmsFile->image_height,
                        'width' => $this->cmsFile->image_width,
                    ];

                    $fileData['preview'] = Html::img($this->cmsFile->src);
                }
                $this->clientOptions['files'][] = $fileData;
            }

        }


        $tools = [];

        foreach ($this->tools as $id => $config)
        {
            $config['id'] = $id;
            $config['ajaxFileUploadWidget'] = $this;
            $tool = \Yii::createObject($config);
            $tools[$id] = $tool;
        }

        $this->tools = $tools;

        if (!$this->tools)
        {
            throw new InvalidConfigException('Not configurated file upload tools');
        }


    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        Html::addCssClass($this->options, 'sx-element');

        if ($this->multiple)
        {
            $items = [];
            if ($this->model->{$this->attribute})
            {
                $items = ArrayHelper::map($this->getCmsFiles(), 'id', 'id');
            }

            $element = $this->hasModel()
                ? Html::activeListBox($this->model, $this->attribute, $items, $this->options)
                : Html::hiddenInput($this->name, $this->value, $this->options);
        } else
        {
            $element = $this->hasModel()
                ? Html::activeHiddenInput($this->model, $this->attribute, $this->options)
                : Html::hiddenInput($this->name, $this->value, $this->options);
        }


        echo $this->render($this->view_file, [
            'element'         => $element,
        ]);
    }

    /**
     * @return null|CmsStorageFile
     */
    public function getCmsFile()
    {
        if ($fileId = $this->model->{$this->attribute})
        {
            return CmsStorageFile::findOne((int) $fileId);
        }

        return null;
    }

    /**
     * @return null|CmsStorageFile[]
     */
    public function getCmsFiles()
    {
        if ($fileId = $this->model->{$this->attribute})
        {
            return CmsStorageFile::find()->where(['id' => $fileId])->all();
        }

        return null;
    }

    /**
     * @return AjaxFileUploadDefaultTool
     */
    public function getDefaultTool()
    {
        return reset($this->tools);
    }
}