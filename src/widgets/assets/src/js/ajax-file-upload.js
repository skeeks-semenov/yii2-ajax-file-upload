/*!
* @author Semenov Alexander <semenov@skeeks.com>
* @link http://skeeks.com/
* @copyright 2010 SkeekS (СкикС)
* @date 27.04.2017
*/
(function(sx, $, _)
{
    sx.createNamespace('classes.fileupload', sx);

    /**
     * Виджет загурзки файлов
     */
    sx.classes.fileupload.AjaxFileUpload = sx.classes.Component.extend({

        _init: function()
        {
            var self    = this;
            //Инструменты загрузки
            this.Tools  = [];
            this.Files  = [];
        },

        _onDomReady: function()
        {
            var self = this;
            
            this.JFiles = $(".sx-files", this.getJWrapper());
            this.JItemTemplate = $(".sx-item-template", this.getJWrapper()).children();
            this.JTools = $(".sx-tools", this.getJWrapper());
            this.JElement = $(".sx-element", this.getJWrapper());

            //Запуск инструмента загрузки
            this.JRunToolBtn = $(".sx-run-tool", this.getJWrapper());
            this.JRunToolBtn.on('click', function()
            {
                var id = $(this).data('tool-id');
                var Tool = self.getTool(id);
                if (!Tool || !Tool instanceof sx.classes.fileupload.tools._Tool)
                {
                    throw new Error('Tool not found or bad: ' + id);
                    return false;
                }
                Tool.run();
                return false;
            });

            if (this.get('files'))
            {
                _.each(this.get('files'), function(filedata)
                {
                    var File = new sx.classes.fileupload.File(self, filedata);
                    self.appendFile(File);
                });
            }
        },

        /**
         * @returns {*}
         */
        getJWrapper: function()
        {
            return $("#" + this.get('id'));
        },

        /**
         * @param id
         */
        getTool: function(id)
        {
            return _.find(this.Tools, function(Tool)
            {
                return Tool.get('id') == id;
            });
        },

        /**
         * @returns {*}
         */
        getFileStates: function()
        {
            return new sx.classes.Entity( this.get('fileStates') );
        },

        /**
         * @returns {boolean}
         */
        isMultiple: function()
        {
            return Boolean(this.get('multiple'));
        },



        /**
         * @param File
         * @returns {sx.classes.fileupload.AjaxFileUpload}
         */
        appendFile: function(NewFile)
        {
            var self = this;

            if (this.isMultiple())
            {
                this.Files.push(NewFile);
                this.JFiles.append(NewFile.render());

                NewFile.onValue(function()
                {
                    self.change();
                });

                return this;

            } else
            {
                self.JElement.val('');
                self.JElement.change();

                _.each(this.Files, function(File)
                {
                    File.remove();
                });

                this.Files.push(NewFile);
                this.JFiles.append(NewFile.render());

                NewFile.onValue(function()
                {
                    self.change();
                });

                return this;
            }
        },

        change: function()
        {
            var self = this;

            if (this.isMultiple())
            {
                self.JElement.empty();

                _.each(this.Files, function(File)
                {
                    self.JElement.append(
                        $("<option>", {'value': File.getValue(), 'selected': 'selected'}).append(File.getValue())
                    )
                });

            } else
            {
                self.JElement.val('');

                _.each(this.Files, function(File)
                {
                    self.JElement.val(File.getValue());
                });
            }

            self.JElement.change();
            this.trigger('change');
            return this;
        },

        /**
         * @param id
         * @returns {sx.classes.fileupload.AjaxFileUpload}
         */
        removeFile: function(id)
        {
            var newFiles = [];

            _.each(this.Files, function(File)
            {
                if (File.get('id') != id)
                {
                    newFiles.push(File);
                }
            });

            this.Files = newFiles;
            this.change();
            return this;
        }
    });

})(sx, sx.$, sx._);