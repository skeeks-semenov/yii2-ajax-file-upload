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
        //$tmpPath = Yii::getPathOfAlias('application.tmp');
        $tmpPath = \Yii::getAlias(\Yii::$app->getModule($this->moduleId)->private_tmp_dir);

        if (!is_dir($tmpPath)) {
            return false;
        }

        $now = microtime(true);
        // Find old files
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpPath), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $name => $fileObject)
        {
			$filename = $fileObject->getRealPath();
			if (is_dir($filename)) {
			    continue;
            }

            $bn = basename($filename);
            $dn = dirname($filename);

            if (substr($bn, 0, 1) == '.') {
                if ($dn == $tmpPath) {
                    continue;
                }
            }

            $ft = filemtime($filename);

            if ($now - $ft > $expires) {

				if ($verbose) {
                    $this->stdout('Remove old file: ' . $filename . "\n");
                }

                unlink($filename);
            }
        }
        // Find old dirs
        foreach (scandir($tmpPath) as $filename)  {

            if ($filename == '.' || $filename == '..') {
                continue;
            }

            $full_path = $tmpPath . '/' . $filename;

            if (!is_dir($full_path)) {
                continue;
            }

            $ft = filemtime($full_path);

            if ($now - $ft > $expires) {
				if ($verbose) {
                    $this->stdout('Remove old directory: ' . $filename . "\n");
                }

                FileHelper::removeDirectory($full_path);
            }
        }
    }
}
