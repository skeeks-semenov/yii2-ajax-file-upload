<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 29.04.2017
 */

namespace skeeks\yii2\ajaxfileupload\controllers;

use skeeks\sx\helpers\ResponseHelper;
use skeeks\yii2\vkDatabase\models\VkCity;
use skeeks\imagine\Image;
use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\validators\UrlValidator;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Class UploadController
 *
 * @package skeeks\yii2\ajaxfileupload\controllers
 */
class UploadController extends Controller
{
    public $defaultAction           = 'upload';

    public $local_root_tmp_dir      = '@frontend/web/assets/temp';
    public $local_public_tmp_dir    = '/assets/temp';

    public function init()
    {
        parent::init();
    }

    /**
     * @return ResponseHelper
     */
    public function actionUpload()
    {
        //sleep(5);
        $rr = new ResponseHelper();
        try
        {
            $file = UploadedFile::getInstanceByName(\Yii::$app->request->post('formName'));

            $uid = uniqid(time(), true);
            $directory = \Yii::getAlias($this->local_root_tmp_dir) . DIRECTORY_SEPARATOR . $uid . DIRECTORY_SEPARATOR;
            if (!is_dir($directory))
            {
                FileHelper::createDirectory($directory);
            }

            if (!is_dir($directory))
            {
                throw new Exception(\Yii::t('app', 'Could not create a directory for download'));
            }

            if ($file && \Yii::$app->request->post('formName'))
            {
                $rootPath = $directory . $file->name;
                if (!$file->saveAs($rootPath))
                {
                    throw new Exception(\Yii::t('app', 'Could not upload the image to a local folder'));
                }

                $src = $this->local_public_tmp_dir . '/' . $uid . "/" . $file->name;
                $rr->success = true;
                $data = [
                    'name'          =>  $file->name,
                    "src"           =>  $src,
                    "rootPath"      =>  $rootPath,
                ];

            } else if ($link = \Yii::$app->request->post('link'))
            {
                $errors = '';
                if (!(new UrlValidator())->validate($link, $errors))
                {
                    throw new Exception($errors);
                }

                $client = new Client();
                $response = $client->createRequest()
                    ->setMethod('get')
                    ->setUrl($link)
                    ->send();

                if (!$response->isOk) {
                    throw new Exception( \Yii::t('skeeks/yii2-ajaxfileupload', 'File not available for download') );
                }

                $clearLink = $link;
                if ($pos = strpos($link, "?"))
                {
                    $link = StringHelper::substr($link, 0, $pos);
                }

                $file_content = $response->content;

                if (!extension_loaded('fileinfo'))
                {
                    throw new Exception( \Yii::t('skeeks/yii2-ajaxfileupload', 'PHP fileinfo is not installed') );
                }

                $extension  = pathinfo($link, PATHINFO_EXTENSION);
                $fileName   = pathinfo($link, PATHINFO_BASENAME);

                if (!$fileName)
                {
                    throw new Exception( \Yii::t('skeeks/yii2-ajaxfileupload', 'Could not determine file name') );
                }

                $rootPath = $directory . $fileName;
                $is_file_saved = file_put_contents($rootPath, $file_content);

                if (!$is_file_saved)
                {
                    throw new Exception( \Yii::t('skeeks/yii2-ajaxfileupload', 'Could not save file') );
                }

                try
                {
                    $mimeType = FileHelper::getMimeType($rootPath, null, false);
                } catch (InvalidConfigException $e)
                {
                    throw new Exception( \Yii::t('skeeks/yii2-ajaxfileupload', 'Could not determine file extension:') . " " . $e->getMessage());
                }

                if (!$mimeType)
                {
                    throw new Exception( \Yii::t('skeeks/yii2-ajaxfileupload', 'Could not determine file mimeType') );
                }


                if (!$extension)
                {
                    $extensions = FileHelper::getExtensionsByMimeType($mimeType);
                    if ($extensions)
                    {
                        if (in_array("jpg", $extensions))
                        {
                            $extension = 'jpg';
                        } else if (in_array("png", $extensions))
                        {
                            $extension = 'png';
                        } else
                        {
                            $extension = $extensions[0];
                        }

                        $fileName = $fileName . "." . $extension;


                        $rootPath = $directory . $fileName;
                        $is_file_saved = file_put_contents($rootPath, $file_content);

                        if (!$is_file_saved)
                        {
                            throw new Exception( \Yii::t('skeeks/yii2-ajaxfileupload', 'Could not save file') );
                        }
                    }
                }


                $src = $this->local_public_tmp_dir . '/' . $uid . "/" . $fileName;
                $rr->success = true;

                $data = [
                    'name'          =>  $fileName,
                    "src"           =>  $src,
                    "rootPath"      =>  $rootPath,
                ];
            }

            $size = filesize($rootPath);
            $mimeType = FileHelper::getMimeType($rootPath);

            $data['type'] = $mimeType;
            $type = $mimeType ? explode("/", $mimeType)[0] : "";
            $data['size'] = $size;
            $data['sizeFormated'] = \Yii::$app->formatter->asShortSize($size);

            if ($type == 'image')
            {
                $image = Image::getImagine()->open($rootPath);
                $data['image'] = [
                    'height' => $image->getSize()->getHeight(),
                    'width' => $image->getSize()->getWidth(),
                ];
            }

            $rr->data = $data;

        } catch (\Exception $e)
        {
            \Yii::error($e->getMessage(), static::class);
            $rr->message = $e->getMessage();
            $rr->success = false;
        }

        return $rr;
    }
}
