<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 31.08.2017
 */
namespace skeeks\yii2\ajaxfileupload\console\controllers;
use yii\console\Controller;
use yii\helpers\FileHelper;

/**
 * Class CleanupController
 *
 * @package skeeks\yii2\ajaxfileupload\console\controllers
 */
class CleanupController extends Controller
{
    public $defaultAction = 'run';

    public $moduleId = 'ajaxfileupload';
    /**
     * @param int $expires
     * @param int $verbose
     */
    public function actionRun($expires = 3600, $verbose = 1)
    {
        $path = \Yii::getAlias(\Yii::$app->getModule($this->moduleId)->private_tmp_dir);
        $result = (new \skeeks\yii2\ajaxfileupload\services\TemporaryFileCleanup())->run($path, (int)$expires);
        if ($verbose) { $this->stdout(json_encode($result).PHP_EOL); }
        return \yii\console\ExitCode::OK;
    }
}
