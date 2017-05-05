/*!
* @author Semenov Alexander <semenov@skeeks.com>
* @link http://skeeks.com/
* @copyright 2010 SkeekS (СкикС)
* @date 27.04.2017
*/
(function(sx, $, _)
{
    /**
     * Стандартная ajax загрузка файлов
     */
    sx.classes.fileupload.tools.DefaultUploadTool = sx.classes.fileupload.tools._Tool.extend({

        run: function()
        {
            $('#' + this.get('id')).click();
            return false;
        },

        _onDomReady: function()
        {
            var self = this;

            //@see https://github.com/blueimp/jQuery-File-Upload/wiki/Drop-zone-effects
            $(document).bind('drop dragover', function (e) {
                e.preventDefault();
            });

            $(document).bind('dragover', function (e) {
                var dropZone = $('.dropzone'),
                    timeout = window.dropZoneTimeout;
                if (!timeout) {
                    dropZone.addClass('in');
                } else {
                    clearTimeout(timeout);
                }
                var found = false,
                    node = e.target;
                do {
                    if (node === dropZone[0]) {
                        found = true;
                        break;
                    }
                    node = node.parentNode;
                } while (node != null);
                if (found) {
                    dropZone.addClass('hover');
                } else {
                    dropZone.removeClass('hover');
                }
                window.dropZoneTimeout = setTimeout(function () {
                    window.dropZoneTimeout = null;
                    dropZone.removeClass('in hover');
                }, 100);
            });

            this.JInput = $('#' + this.get('id'));

            jQuery(this.JInput).fileupload(
                _.extend(this.get('uploadfile'), {
                    formData: {
                        'formName' : this.get('id')
                    }
                })
            );
            jQuery(this.JInput).on('fileuploadadd', function(e, data) {

                var FileObject = new sx.classes.fileupload.File(self.Uploader);

                $.each(data.files, function (index, file) {

                    FileObject.set('fileinfo', file);
                    FileObject.set('name', file.name);
                    FileObject.set('state', 'queue');
                });

                data.context = FileObject;
                self.Uploader.appendFile(FileObject);
            });

            jQuery(this.JInput).on('fileuploadprocessalways', function(e, data) {
                var FileObject = data.context;

                var index = data.index,
                file = data.files[index];

                if (file.preview) {
                    FileObject.set('preview', file.preview);
                }

                if (file.error) {
                    FileObject.set('error', file.error);
                }

                FileObject.render();
            });

            jQuery(this.JInput).on('fileuploaddone', function(e, data) {
                var FileObject = data.context;

                if (data.result.success === true)
                {
                    FileObject.set('error', '');
                    FileObject.set('state', 'success');

                    FileObject.merge(data.result.data)
                    FileObject.setValue(data.result.data.value);
                } else
                {
                    FileObject.set('error', data.result.message);
                    FileObject.set('state', 'fail');
                }

                FileObject.render();
            });

            jQuery(this.JInput).on('fileuploadsend', function(e, data) {
                var FileObject = data.context;

                if (FileObject.isRemoved)
                {
                    return false;
                }

                FileObject.set('state', 'process');
                FileObject.render();

            });

            jQuery(this.JInput).on('fileuploadfail', function(e, data) {
                var FileObject = data.context;
                FileObject.set('state', 'fail');
                FileObject.set('error', 'File upload failed');
                FileObject.render();
            });


            /*jQuery(this.JInput).on('fileuploadprogressall', function(e, data) {
                console.log("fileuploadprogressall");
                console.log(e);
                console.log(data);
            });

            jQuery(this.JInput).on('fileuploadsubmit', function(e, data) {
                console.log("fileuploadsubmit");
                console.log(e);
                console.log(data);
            });

            jQuery(this.JInput).on('fileuploadprocess', function(e, data) {
                console.log("fileuploadprocess");
                console.log(e);
                console.log(data);
            });

            jQuery(this.JInput).on('fileuploadstart', function(e, data) {
                console.log("fileuploadstart");
                console.log(e);
                console.log(data);
            });

            jQuery(this.JInput).on('fileuploadstop', function(e, data) {
                console.log("fileuploadstop");
                console.log(e);
                console.log(data);
            });

            jQuery(this.JInput).on('paste', function(e, data) {
                console.log("paste");
                console.log(e);
                console.log(data);
            });

            jQuery(this.JInput).on('drop', function(e, data) {
                console.log("drop");
                console.log(e);
                console.log(data);
            });

            jQuery(this.JInput).on('dragover', function(e, data) {
                console.log("dragover");
                e.preventDefault();
                console.log(e);
                console.log(data);
            });*/
        },

    });

})(sx, sx.$, sx._);