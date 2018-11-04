<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 26.04.2017
 */

namespace skeeks\yii2\ajaxfileupload\widgets;

use Imagine\Image\Box;
use skeeks\imagine\Image;
use skeeks\yii2\ajaxfileupload\AjaxFileUploadModule;
use skeeks\yii2\models\CmsStorageFile;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\InputWidget;

/**
 * @property AjaxFileUploadModule|null                    $module
 * @property AjaxFileUploadDefaultTool|AjaxFileUploadTool $defaultTool
 *
 * Class AjaxFileUploadWidget
 * @package skeeks\yii2\widgets\fileupload
 */
class AjaxFileUploadWidget extends InputWidget
{
    public static $autoIdPrefix = 'AjaxFileUploadWidget';

    public $view_file = '@skeeks/yii2/ajaxfileupload/widgets/views/default';

    public $upload_url = ['/ajaxfileupload/upload'];

    public $multiple = false;

    public $accept = ''; //'image/*';

    /**
     * @var AjaxFileUploadDefaultTool[]
     */
    public $tools = [

        'default' =>
            [
                'class' => AjaxFileUploadDefaultTool::class,
                'name'  => 'Загрузить',
                'icon'  => 'glyphicon glyphicon-download-alt',
            ],

        'remote' =>
            [
                'class' => AjaxFileUploadRemoteTool::class,
                'name'  => 'Загрузить по ссылке',
                'icon'  => 'glyphicon glyphicon-globe',
            ],
    ];

    public $clientOptions = [];

    /**
     * @var array
     */
    public $itemOptions = [
        'tag'   => 'div',
        'class' => 'sx-file',
    ];

    public function init()
    {
        parent::init();

        if (!$this->hasModel()) {
            throw new InvalidConfigException('Invalid config');
        }

        AjaxFileUploadModule::registerTranslations();

        $this->upload_url = Url::to($this->upload_url);

        $this->options['multiple'] = $this->multiple;
        $this->clientOptions['multiple'] = $this->multiple;
        $this->clientOptions['id'] = $this->id;

        $this->clientOptions['fileStates'] = [
            'queue'   => \Yii::t('skeeks/yii2-ajaxfileupload', 'Queue'),
            'process' => \Yii::t('skeeks/yii2-ajaxfileupload', 'Loading'),
            'fail'    => \Yii::t('skeeks/yii2-ajaxfileupload', 'Fail'),
            'success' => \Yii::t('skeeks/yii2-ajaxfileupload', 'Success'),
        ];

        $this->_initClientFiles();

        $tools = [];

        foreach ($this->tools as $id => $config) {
            $config['id'] = $id;
            $config['ajaxFileUploadWidget'] = $this;
            $tool = \Yii::createObject($config);
            $tools[$id] = $tool;
        }

        $this->tools = $tools;

        if (!$this->tools) {
            throw new InvalidConfigException('Not configurated file upload tools');
        }
    }


    protected function _initClientFiles()
    {
        if ($this->multiple) {
            if ($value = $this->model->{$this->attribute}) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $this->clientOptions['files'][] = $this->_getClientFileData($val);
                    }
                }
            }
        } else {
            if ($value = $this->model->{$this->attribute}) {
                $this->clientOptions['files'][] = $this->_getClientFileData($value);
            }
        }
    }

    /**
     * @param $value
     * @return array
     */
    protected function _getClientFileData($value)
    {
        $fileData = null;

        if (file_exists($value)) {
            //Root file
            $name = pathinfo($value, PATHINFO_BASENAME);
            $dirname = pathinfo($value, PATHINFO_DIRNAME);

            $dirData = explode('/', $dirname);

            $mimeType = FileHelper::getMimeType($value, null, false);
            $size = filesize($value);
            $fileData = [
                'name'         => $name,
                'value'        => $value,
                'state'        => 'success',
                'size'         => $size,
                'sizeFormated' => \Yii::$app->formatter->asShortSize($size),
                'type'         => $mimeType,
            ];

            $type = $mimeType ? explode("/", $mimeType)[0] : "";

            if ($type == 'image') {
                $image = Image::getImagine()->open($value);
                $fileData['image'] = [
                    'height' => $image->getSize()->getHeight(),
                    'width'  => $image->getSize()->getWidth(),
                ];

                $previewHeight = $image->getSize()->getHeight();
                $previewWidth = $image->getSize()->getWidth();

                if ($image->getSize()->getHeight() > 200) {
                    $previewHeight = 200;
                    $proportion = $previewHeight / $image->getSize()->getHeight();
                    $previewWidth = $previewWidth * $proportion;
                }

                $fileData['src'] = "data:image/png;base64,".base64_encode($image->resize(new Box($previewWidth, $previewHeight))->get('png'));
            }
        }


        return $fileData;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        Html::addCssClass($this->options, 'sx-element');

        if ($this->multiple) {
            $items = [];
            /*if ($this->model->{$this->attribute})
            {
                $items = ArrayHelper::map($this->getCmsFiles(), 'id', 'id');
            }*/

            $element = $this->hasModel()
                ? Html::activeListBox($this->model, $this->attribute, $items, $this->options)
                : Html::hiddenInput($this->name, $this->value, $this->options);
        } else {
            $element = $this->hasModel()
                ? Html::activeHiddenInput($this->model, $this->attribute, $this->options)
                : Html::hiddenInput($this->name, $this->value, $this->options);
        }


        echo $this->render($this->view_file, [
            'element' => $element,
        ]);
    }

    /**
     * @return AjaxFileUploadDefaultTool
     */
    public function getDefaultTool()
    {
        return reset($this->tools);
    }

    /**
     * @return AjaxFileUploadModule|null
     */
    public function getModule()
    {
        return \Yii::$app->getModule('ajaxfileupload');
    }

    /**
     * @return string
     */
    public function renderItemTemplate()
    {
        $options = $this->itemOptions;
        $tag = ArrayHelper::remove($options, 'tag', 'div');

        return Html::tag($tag, '', $options);
    }
}