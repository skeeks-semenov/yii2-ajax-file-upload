<?php
define('YII_ENABLE_ERROR_HANDLER', false);
$vendor=(getenv('SKEEKS_APP_ROOT') ?: '/app').'/vendor';
require $vendor.'/autoload.php';require $vendor.'/yiisoft/yii2/Yii.php';
$root=sys_get_temp_dir().'/upload-cleanup-'.bin2hex(random_bytes(6));
mkdir($root);mkdir($root.'/old');mkdir($root.'/empty');
$checks=0;
$check=static function($ok,$message)use(&$checks){if(!$ok)throw new RuntimeException($message);++$checks;};
try {
    foreach(['expired','old/expired','old/fresh','.keep'] as $name){file_put_contents($root.'/'.$name,'test');}
    foreach(['expired','old/expired','.keep','old','empty'] as $name){touch($root.'/'.$name,time()-7200);}
    $outside=$root.'-outside';file_put_contents($outside,'keep');touch($outside,time()-7200);
    symlink($outside,$root.'/link');
    $service=new skeeks\yii2\ajaxfileupload\services\TemporaryFileCleanup();
    $beats=0;
    $result=$service->run($root,3600,static function($p)use(&$beats){++$beats;});
    $check($result['processed']===2,'Only expired regular files removed');
    $check(is_file($root.'/old/fresh'),'Fresh upload inside old directory preserved');
    $check(is_file($root.'/.keep'),'Top-level dotfile preserved');
    $check(is_file($outside) && is_link($root.'/link'),'Symlink and target preserved');
    $check(!is_dir($root.'/empty'),'Empty expired directory removed');
    $check($beats>2,'Streaming traversal reports checkpoints');
    file_put_contents($root.'/cancelled','test');touch($root.'/cancelled',time()-7200);
    try{$service->run($root,3600,static function(){throw new RuntimeException('cancel');});}
    catch(RuntimeException $e){$check(is_file($root.'/cancelled'),'Cancellation before deletion preserves files');}
    $check($service->run($root.'/absent')['processed']===0,'Absent temp directory is a no-op');
    echo "PASS: $checks upload cleanup checks\n";
} finally {yii\helpers\FileHelper::removeDirectory($root);if(isset($outside))unlink($outside);}
