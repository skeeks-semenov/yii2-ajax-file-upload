/*!
* @author Semenov Alexander <semenov@skeeks.com>
* @link http://skeeks.com/
* @copyright 2010 SkeekS (СкикС)
* @date 27.04.2017
*/
(function(sx, $, _)
{
    sx.createNamespace('classes.fileupload.tools', sx);

    /**
     * Files upload tool
     */
    sx.classes.fileupload.tools._Tool = sx.classes.Component.extend({

        run: function()
        {},

        /**
         * @param AjaxFileUpload
         * @param opts
         */
        construct: function(AjaxFileUpload, opts)
        {
            var self = this;

            if (! (AjaxFileUpload instanceof sx.classes.fileupload.AjaxFileUpload))
            {
                throw new Error('Upload manager not uploaded');
            }

            opts = opts || {};
            this.Uploader = AjaxFileUpload;
            
            AjaxFileUpload.Tools.push(this);

            this.applyParentMethod(sx.classes.Component, 'construct', [opts]);
        },
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        

        _init: function()
        {
            var self = this;

            this.allFiles = 0;
            this.elseFiles = 0;

            this.bind("startUpload", function(e, data)
            {
                self.allFiles   = Number(data.queueLength);
                self.elseFiles  = Number(data.queueLength);
            });

            this.bind("completeUploadFile", function(e, data)
            {
                self.elseFiles = self.elseFiles - 1;
                var uploadedFiles = (self.allFiles - self.elseFiles);
                var pct = (uploadedFiles * 100)/self.allFiles;

                self.triggerOnProgress({
                    'pct': pct,
                    'elseFiles': self.elseFiles,
                    'allFiles': self.allFiles,
                    'uploadedFiles': uploadedFiles,
                });
            });

            this._initManagerEvents();
            this._afterInit();
        },

        _afterInit: function()
        {},

        _initManagerEvents: function()
        {
            var self = this;

            this.bind("error", function(e, message)
            {
                self.getUploader().trigger("error", message);
            });

            this.bind("completeUpload", function(e, data)
            {
                self.getUploader().trigger('completeUpload', data);
            });

            this.bind("startUpload", function(e, data)
            {
                //queueLength
                self.getUploader().trigger('startUpload', data);
            });

            this.bind("startUploadFile", function(e, data)
            {
                //queueLength
                self.getUploader().trigger('startUploadFile', data);
            });

            this.bind("completeUploadFile", function(e, data)
            {
                //queueLength
                self.getUploader().trigger('completeUploadFile', data);
            });

            this.bind("onProgressFile", function(e, data)
            {
                //queueLength
                self.getUploader().trigger('onProgressFile', data);
            });

            this.bind("onProgress", function(e, data)
            {
                //queueLength
                self.getUploader().trigger('onProgress', data);
            });
        },

        /**
         *
         * @returns {sx.classes.fileupload.AjaxFileUpload}
         */
        getUploader: function()
        {
            return this.get("Uploader");
        },

        /**
         * @returns {string}
         */
        getRandStr: function()
        {
            var result       = '';
            var words        = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
            var max_position = words.length - 1;
                for( i = 0; i < 6; ++i ) {
                    position = Math.floor ( Math.random() * max_position );
                    result = result + words.substring(position, position + 1);
                }
            return result;
        },



        /**
         * Начало выполнения загрузки файлов
         *
         * @param data
         * @returns {sx.classes.files.sources.Base}
         */
        triggerStartUpload: function(data)
        {
            this.trigger("startUpload", data);
            return this;
        },



        /**
         * Все файлы загружены процесс остановлен
         *
         * @param data
         * @returns {sx.classes.files._Source}
         */
        triggerCompleteUpload: function(data)
        {
            this.trigger("completeUpload", data);
            return this;
        },

        /**
         * Начало загрузки файла
         *
         * @param data
         * @returns {sx.classes.files.sources.Base}
         */
        triggerStartUploadFile: function(data)
        {
            this.trigger("startUploadFile", data);
            return this;
        },

        /**
         * завершение загрузки файла
         *
         * @param data
         * @returns {sx.classes.files.sources.Base}
         */
        triggerCompleteUploadFile: function(data)
        {
            this.trigger("completeUploadFile", data);
            return this;
        },

        /**
         * @param data
         * @returns {sx.classes.files._Source}
         */
        triggerOnProgress: function(data)
        {
            this.trigger("onProgress", data);
            return this;
        },

        /**
         * Процесс загрузки файла
         *
         * @param data
         * @returns {sx.classes.files.sources.Base}
         */
        triggerOnProgressFile: function(data)
        {
            this.trigger("onProgressFile", data);
            return this;
        },

        /**
         * Произошла ошибка
         *
         * @param msg
         * @returns {sx.classes.files.sources.Base}
         */
        triggerError: function(data)
        {
            this.trigger("error", data);
            return this;
        },

    });

})(sx, sx.$, sx._);