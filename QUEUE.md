# Temporary upload cleanup service

TemporaryFileCleanup is shared by the legacy ajaxfileupload/cleanup CLI and the
CMS-owned native CleanupTemporaryFilesJobHandler. This Yii upload package does
not depend on cms-job or register a CMS schedule. The CMS package owns that
integration and requires the service-bearing upload release (2.0.2).

Expiry defaults to one hour. Traverse without following symlinks, preserve
top-level dotfiles, delete only expired regular files, and remove only empty
expired directories. Never recursively delete an old directory: it may contain
a fresh or concurrently created upload. Failures and cancellation propagate.
No temporary absolute paths are exposed in job results.

tests/temporary-cleanup-smoke.php creates and removes only its own random
temporary fixture directory, including fresh nested files and a symlink.
