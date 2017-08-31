Yii2 file upload
===================================

Installation
------------

Either run

```
php composer.phar require --prefer-dist skeeks/yii2-ajax-file-upload "*"
```

or add

```
"skeeks/yii2-ajax-file-upload": "*"
```

```
"repositories": [
    {
        "type": "git",
        "url":  "https://github.com/skeeks-semenov/yii2-ajax-file-upload.git"
    }
]
```

Configuration web app
----------


```php

'modules' => 
[
    'ajaxfileupload' => [
        'class'         => 'skeeks\yii2\ajaxfileupload\AjaxFileUploadModule',
    
        'controllerMap' => [
            'upload' => [
                'class'                 => 'skeeks\yii2\ajaxfileupload\controllers\UploadController',
                'private_tmp_dir'       => '@runtime/ajaxfileupload',
            ]
        ]
    ]
]

```

Configuration console app
----------


```php

'modules' => 
[
    'ajaxfileupload' => [
        'class'                 => 'skeeks\yii2\ajaxfileupload\AjaxFileUploadModule',
        'controllerNamespace'   => 'skeeks\yii2\ajaxfileupload\console\controllers'
    
        'controllerMap' => [
            'upload' => [
                'class'                 => '\skeeks\yii2\ajaxfileupload\controllers\UploadController',
                'private_tmp_dir'       => '@runtime/ajaxfileupload',
            ]
        ]
    ]
]

```


Console commands
----------

```bash

```


##Links
* [Web site (rus)](https://cms.skeeks.com)
* [Author](https://skeeks.com)
* [ChangeLog](https://github.com/skeeks-cms/cms-vk-database/blob/master/CHANGELOG.md)


___

> [![skeeks!](https://gravatar.com/userimage/74431132/13d04d83218593564422770b616e5622.jpg)](https://skeeks.com)
<i>SkeekS CMS (Yii2) — quickly, easily and effectively!</i>  
[skeeks.com](https://skeeks.com) | [cms.skeeks.com](https://cms.skeeks.com)


