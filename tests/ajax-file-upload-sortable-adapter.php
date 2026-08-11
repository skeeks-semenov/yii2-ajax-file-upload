<?php

function ajaxFileUploadSortableExpect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$view = file_get_contents(dirname(__DIR__).'/src/widgets/views/default.php');
$script = file_get_contents(dirname(__DIR__).'/src/widgets/assets/src/js/ajax-file-upload.js');
$composer = json_decode(file_get_contents(dirname(__DIR__).'/composer.json'), true);

ajaxFileUploadSortableExpect(
    ($composer['require']['skeeks/cms-backend'] ?? null) === '^2.0 || dev-master@dev',
    'Ajax file upload does not declare its backend adapter dependency.'
);
ajaxFileUploadSortableExpect(
    strpos($view, 'if ($widget->multiple)') !== false
    && strpos($view, 'BackendSortableAdapterAsset::register($this);') !== false,
    'Ajax file upload does not register the adapter conditionally for multiple mode.'
);
ajaxFileUploadSortableExpect(
    strpos($script, 'sx.backend.sortable.create(this.JFiles') !== false
    && strpos($script, 'itemSelector: "> .sx-file"') !== false
    && strpos($script, 'onUpdate: function()') !== false
    && strpos($script, 'self.run();') !== false,
    'Ajax file upload does not rebuild its submitted values after adapter sorting.'
);
ajaxFileUploadSortableExpect(
    strpos($script, '.sortable(') === false
    && strpos($view, '\\yii\\jui\\Sortable::widget()') === false,
    'Ajax file upload still uses jQuery UI Sortable directly.'
);

echo "Ajax file upload sortable adapter contract: OK\n";
