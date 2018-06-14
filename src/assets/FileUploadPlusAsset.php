<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 26.04.2017
 */

namespace skeeks\yii2\ajaxfileupload\assets;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class FileUploadPlusAsset extends \dosamigos\fileupload\FileUploadPlusAsset
{
    public $publishOptions = [
        'except' => [
            'server/*',
            'test'
        ],
    ];
}