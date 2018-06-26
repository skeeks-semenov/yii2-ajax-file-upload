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

            this.isProcess = false;
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


            if (this.isMultiple()) {
                this.JFiles.sortable(
                {
                    cursor: "move",
                    //handle: ".sx-tree-move",
                    forceHelperSize: true,
                    forcePlaceholderSize: true,
                    opacity: 0.5,
                    grid: [ 2, 2 ],

                    update: function( event, ui )
                    {
                        self.change();
                    }
                    /*placeholder: "portlet-placeholder ui-corner-all"*/
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

            this.trigger('addFile', {'file' : NewFile});

            if (!NewFile.getValue() && this.isProcess === false) {

                this.isProcess = true;
                this.trigger('startUpload');

            }

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

                $('.sx-value-element', this.JFiles).each(function(el, key) {
                    self.JElement.append(
                        $("<option>", {'value': $(this).data('value'), 'selected': 'selected'}).append($(this).data('value'))
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


            var allUploaded = true;

            _.each(this.Files, function(File)
            {
                if (!File.getValue()) {
                    allUploaded = false;
                }
            });

            if (allUploaded === true) {
                this.trigger('endUpload');
            }

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