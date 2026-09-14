<?php
namespace skeeks\yii2\ajaxfileupload\services;

use yii\base\BaseObject;

/** Removes expired regular files without following symlinks or deleting fresh uploads. */
class TemporaryFileCleanup extends BaseObject
{
    public function run(string $path, int $expires = 3600, ?callable $checkpoint = null): array
    {
        if ($expires < 0) { throw new \InvalidArgumentException('expires must not be negative.'); }
        $processed = 0;
        $directories = 0;
        $notify = static function () use ($checkpoint, &$processed) {
            if ($checkpoint) { $checkpoint(['stage' => 'temporary_files', 'processed' => $processed]); }
        };
        $notify();
        if (!is_dir($path)) { return ['processed' => 0, 'directories' => 0]; }
        if (is_link($path)) { throw new \RuntimeException('Temporary root must not be a symlink.'); }
        $root = realpath($path);
        if ($root === false || $root === DIRECTORY_SEPARATOR) { throw new \RuntimeException('Invalid temporary root.'); }
        $cutoff = microtime(true) - $expires;
        $oldDirectories = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            $notify();
            $name = $file->getPathname();
            if ($file->isLink()) { continue; }
            if ($file->isDir()) {
                if ($file->getMTime() < $cutoff) { $oldDirectories[] = $name; }
                continue;
            }
            if (dirname($name) === $root && substr($file->getBasename(), 0, 1) === '.') { continue; }
            clearstatcache(true, $name);
            if (is_file($name) && !is_link($name) && filemtime($name) < $cutoff) {
                if (!unlink($name)) { throw new \RuntimeException('Cannot remove expired upload.'); }
                ++$processed;
                $notify();
            }
        }
        foreach ($oldDirectories as $directory) {
            $notify();
            // Never recursively remove a directory: a concurrent upload may have made it non-empty.
            if (is_dir($directory) && !is_link($directory) && count(scandir($directory)) === 2) {
                if (!@rmdir($directory) && is_dir($directory) && count(scandir($directory)) === 2) {
                    throw new \RuntimeException('Cannot remove empty upload directory.');
                }
                ++$directories;
            }
        }
        return ['processed' => $processed, 'directories' => $directories];
    }
}
