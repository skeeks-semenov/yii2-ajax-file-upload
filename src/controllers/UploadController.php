<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 29.04.2017
 */

namespace skeeks\yii2\ajaxfileupload\controllers;

use Imagine\Image\Box;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\StringHelper;
use skeeks\cms\Skeeks;
use skeeks\imagine\Image;
use skeeks\sx\helpers\ResponseHelper;
use skeeks\yii2\vkDatabase\models\VkCity;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\httpclient\Client;
use yii\validators\UrlValidator;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Class UploadController
 *
 * @package skeeks\yii2\ajaxfileupload\controllers
 */
class UploadController extends Controller
{
    /**
     * @var bool whether to enable CSRF validation for the actions in this controller.
     * CSRF validation is enabled only when both this property and [[\yii\web\Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = false;

    public $defaultAction = 'upload';

    public $private_tmp_dir = '';

    public function init()
    {
        parent::init();

        if (!$this->private_tmp_dir) {
            $this->private_tmp_dir = $this->module->private_tmp_dir;
        }
    }

    /**
     * @return ResponseHelper
     */
    public function actionUpload()
    {
        //sleep(5);

        $rr = new ResponseHelper();
        try {

            Skeeks::unlimited();
            /*set_time_limit(0);
            ini_set("memory_limit", "50G");*/


            $file = UploadedFile::getInstanceByName(\Yii::$app->request->post('formName'));

            $uid = uniqid(time(), true);
            $directory = \Yii::getAlias($this->private_tmp_dir).DIRECTORY_SEPARATOR.$uid.DIRECTORY_SEPARATOR;
            if (!is_dir($directory)) {
                FileHelper::createDirectory($directory);
            }

            if (!is_dir($directory)) {
                throw new Exception(\Yii::t('app', 'Could not create a directory for download'));
            }

            if ($file && \Yii::$app->request->post('formName')) {
                $rootPath = $directory.$file->name;

                if ($file->hasError) {

                    throw new Exception(\Yii::t('app', 'Ошибка: '.$file->error));
                }

                if (!$file->saveAs($rootPath, false)) {
                    throw new Exception(\Yii::t('app', 'Could not upload the image to a local folder'));
                }

                $rr->success = true;
                $data = [
                    'name'  => $file->name,
                    "value" => $rootPath,
                ];

            } else if ($link = \Yii::$app->request->post('link')) {
                $errors = '';
                if (!(new UrlValidator())->validate($link, $errors)) {
                    throw new Exception($errors);
                }

                $client = new Client();
                $response = $client->createRequest()
                    ->setMethod('get')
                    ->setUrl($link)
                    ->send();

                if (!$response->isOk) {
                    throw new Exception(\Yii::t('skeeks/yii2-ajaxfileupload', 'File not available for download'));
                }

                $clearLink = $link;
                if ($pos = strpos($link, "?")) {
                    $link = StringHelper::substr($link, 0, $pos);
                }

                $file_content = $response->content;

                if (!extension_loaded('fileinfo')) {
                    throw new Exception(\Yii::t('skeeks/yii2-ajaxfileupload', 'PHP fileinfo is not installed'));
                }

                $extension = pathinfo($link, PATHINFO_EXTENSION);

                $fileNameData = $link;

                $fileNameData = str_replace(".", "_", $fileNameData);
                $fileNameData = str_replace("?", "_", $fileNameData);
                $fileNameData = str_replace("&", "_", $fileNameData);
                $fileNameData = str_replace("?", "_", $fileNameData);

                $fileName = pathinfo($fileNameData, PATHINFO_BASENAME);

                if (!$fileName) {
                    throw new Exception(\Yii::t('skeeks/yii2-ajaxfileupload', 'Could not determine file name'));
                }

                $rootPath = $directory.$fileName;
                $is_file_saved = file_put_contents($rootPath, $file_content);

                if (!$is_file_saved) {
                    throw new Exception(\Yii::t('skeeks/yii2-ajaxfileupload', 'Could not save file'));
                }

                try {
                    $mimeType = FileHelper::getMimeType($rootPath, null, false);
                } catch (InvalidConfigException $e) {
                    throw new Exception(\Yii::t('skeeks/yii2-ajaxfileupload', 'Could not determine file extension:')." ".$e->getMessage());
                }

                if (!$mimeType) {
                    throw new Exception(\Yii::t('skeeks/yii2-ajaxfileupload', 'Could not determine file mimeType'));
                }


                //if (!$extension)
                //{
                $extensions = FileHelper::getExtensionsByMimeType($mimeType);
                if ($extensions) {
                    /*var_dump($rootPath);
                    var_dump($is_file_saved);
                    die;*/
                    if (in_array("jpg", $extensions)) {
                        $extension = 'jpg';
                    } else if (in_array("png", $extensions)) {
                        $extension = 'png';
                    } else if (in_array("gif", $extensions)) {
                        $extension = 'gif';
                    } else {
                        if (!$extension) {
                            $extension = $extensions[0];
                        }

                    }

                    $fileName = $fileName.".".$extension;
                    $newRootPath = $directory.$fileName;
                    //$is_file_saved = file_put_contents($rootPath, $file_content);

                    if ($rootPath != $newRootPath) {
                        $is_file_saved = rename($rootPath, $newRootPath);
                        $rootPath = $newRootPath;
                    }

                    if (!$is_file_saved) {
                        throw new Exception(\Yii::t('skeeks/yii2-ajaxfileupload', 'Could not save file'));
                    }
                }
                //}


                $rr->success = true;

                $data = [
                    'name'  => $fileName,
                    "value" => $rootPath,
                ];
            } else {
                //Проверить max_upload_file_size

                throw new Exception("Проверьте настройки php max_upload_file_size + post_max_size");

            }

            $size = filesize($rootPath);
            $mimeType = FileHelper::getMimeType($rootPath);

            $data['type'] = $mimeType;
            $type = $mimeType ? explode("/", $mimeType)[0] : "";
            $data['size'] = $size;
            $data['sizeFormated'] = \Yii::$app->formatter->asShortSize($size);


            if ($type == 'image') {
                try {
                    $image = Image::getImagine()->open($rootPath);

                    $data['image'] = [
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

                    $data['src'] = "data:image/png;base64,".base64_encode($image->resize(new Box($previewWidth, $previewHeight))->get('png'));
                } catch (\Exception $exception) {
                    $content = file_get_contents($rootPath);
                    $data['src'] = "data:{$mimeType};base64,".base64_encode($content);
                }

            }

            $rr->data = $data;

        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), static::class);
            $rr->message = $e->getMessage();
            $rr->success = false;
        }

        return $rr;
    }


    /**
     * Загрузка бинарных данных
     * @return ResponseHelper
     */
    public function actionBin()
    {
        $rr = new RequestResponse();
        $data = [];
        //\Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $id = \Yii::$app->request->getHeaders()->get("sxuploader-upload-id");
            $dirId = StringHelper::strtolower($id);
            $directory = \Yii::getAlias($this->private_tmp_dir).DIRECTORY_SEPARATOR.$dirId.DIRECTORY_SEPARATOR;
            if (!is_dir($directory)) {
                FileHelper::createDirectory($directory);
            }

            if (!is_dir($directory)) {
                throw new Exception(\Yii::t('app', 'Could not create a directory for download'));
            }

            //Корректный запрос
            if ($_SERVER["REQUEST_METHOD"] == "POST") {

                $from = (int)\Yii::$app->request->getHeaders()->get("sxuploader-portion-from");
                $widgetId = (string)\Yii::$app->request->getHeaders()->get("sxuploader-widget-id");
                $fileName = (string)rawurldecode(\Yii::$app->request->getHeaders()->get("sxuploader-file-name"));
                $fileSize = (int)\Yii::$app->request->getHeaders()->get("sxuploader-file-size");
                $isCheckRequest = (int)\Yii::$app->request->getHeaders()->get("sxuploader-check-resume");

                $fileHash = md5(\Yii::$app->session->id.$widgetId.$fileSize);
                $rootPath = $directory.$fileHash.$fileName."-loading";

                //Проверка дозагрузки
                if ($isCheckRequest == 1) {
                    $rr->success = true;
                    if (file_exists($rootPath)) {
                        //Файл в процессе
                        $rr->data = [
                            'is_loading'     => 1,
                            'rootPath'     => $rootPath,
                            'rootPathSize' => filesize($rootPath),
                            'from'         => $from,
                            'jsSize'       => $fileSize,
                        ];
                        return $rr;
                    } else {
                        //файл мог быть уже загружен полностью
                        $newRootPath = substr($rootPath, 0, strlen($rootPath) - 8);
                        //Убрать хэш из названия файла
                        $newRootPath = str_replace($fileHash, "", $newRootPath);
                        $rootPath = $newRootPath;

                        if (file_exists($rootPath)) {
                            $data['is_full'] = 1;
                        } else {

                            return $rr;
                        }
                    }
                } else {
                    //Загрузка файла
                    if ($from == 0) {
                        $fout = fopen($rootPath, "wb");
                    } else {
                        $fout = fopen($rootPath, "ab");
                    }

                    if (!$fout) {
                        throw new Exception(\Yii::t('app', "Can't open file for writing: ").$filename);
                    }


                    if (\Yii::$app->request->rawBody) {
                        fwrite($fout, \Yii::$app->request->rawBody);
                        fclose($fout);
                    }


                    //Файл загружен полностью
                    if (filesize($rootPath) == $fileSize) {
                        $rr->success = true;

                        $newRootPath = substr($rootPath, 0, strlen($rootPath) - 8);
                        //Убрать хэш из названия файла
                        $newRootPath = str_replace($fileHash, "", $newRootPath);

                        rename($rootPath, $newRootPath);
                        $rootPath = $newRootPath;

                    } else {
                        //Это была загрузка лишь одной порции
                        $rr->success = true;

                        $rr->data = [
                            'rootPath'     => $rootPath,
                            'rootPathSize' => filesize($rootPath),
                            'from'         => $from,
                            'jsSize'       => $fileSize,
                        ];
                        return $rr;
                    }

                }

            }


            $size = filesize($rootPath);
            $mimeType = FileHelper::getMimeType($rootPath);

            $data['type'] = $mimeType;
            $type = $mimeType ? explode("/", $mimeType)[0] : "";
            $data['size'] = $size;
            $data['sizeFormated'] = \Yii::$app->formatter->asShortSize($size);
            $data['value'] = $rootPath;
            $data['name'] = $fileName;


            if ($type == 'image') {
                try {
                    $image = Image::getImagine()->open($rootPath);

                    $data['image'] = [
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

                    $data['src'] = "data:image/png;base64,".base64_encode($image->resize(new Box($previewWidth, $previewHeight))->get('png'));
                } catch (\Exception $exception) {
                    $content = file_get_contents($rootPath);
                    $data['src'] = "data:{$mimeType};base64,".base64_encode($content);
                }

            }

            $rr->data = $data;

        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), static::class);
            $rr->message = $e->getMessage();
            $rr->success = false;
        }

        return $rr;
    }
}
