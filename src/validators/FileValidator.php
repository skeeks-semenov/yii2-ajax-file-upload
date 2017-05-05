<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 05.05.2017
 */
namespace skeeks\yii2\ajaxfileupload\validators;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Class FileValidator
 *
 * @package skeeks\yii2\ajaxfileupload\validators
 */
class FileValidator extends \yii\validators\FileValidator
{
    /**
     * @param mixed $value
     *
     * @return array|null
     */
    protected function validateValue($value)
    {
        if (is_string($value) && file_exists($value))
        {
            $uploadFile = new UploadedFile();
            $uploadFile->size = filesize($value);
            $uploadFile->type = FileHelper::getMimeType($value, null, false);
            $uploadFile->tempName = $value;
            $uploadFile->name = $value;

            $value = $uploadFile;
        }

        return parent::validateValue($value);
    }

    public function validateAttribute($model, $attribute)
    {
        //print_r($model->{$attribute});die;
        return parent::validateAttribute($model, $attribute);
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        return '';
    }
}