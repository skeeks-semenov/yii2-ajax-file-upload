Yii2 file upload
===================================

[![Latest Stable Version](https://poser.pugx.org/skeeks/yii2-ajax-file-upload/v/stable.png)](https://packagist.org/packages/skeeks/yii2-ajax-file-upload)
[![Total Downloads](https://poser.pugx.org/skeeks/yii2-ajax-file-upload/downloads.png)](https://packagist.org/packages/skeeks/yii2-ajax-file-upload)

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
php yii ajaxfileupload/cleanup
```


Usage
-----



```php
echo $form->field($model, 'image_src')->widget(
            \skeeks\yii2\ajaxfileupload\widgets\AjaxFileUploadWidget::class,
            [
                'accept' => 'image/*',
                //'view_file' => '@skeeks/yii2/ajaxfileupload/widgets/views/buttons',
                //'itemOptions' => [
                //    'class' => 'col-lg-6 col-md-6 col-sm-6 sx-file sx-state-success'
                //]
            ]
        );

```


Links
-----
* [Web site (rus)](https://cms.skeeks.com)
* [Author](https://skeeks.com)
* [ChangeLog](https://github.com/skeeks-cms/cms-vk-database/blob/master/CHANGELOG.md)


___

> [![skeeks!](https://gravatar.com/userimage/74431132/13d04d83218593564422770b616e5622.jpg)](https://skeeks.com)
<i>SkeekS CMS (Yii2) — quickly, easily and effectively!</i>  
[skeeks.com](https://skeeks.com) | [cms.skeeks.com](https://cms.skeeks.com)


