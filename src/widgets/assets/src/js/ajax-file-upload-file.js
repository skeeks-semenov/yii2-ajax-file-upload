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
     * Files upload tool
     */
    sx.classes.fileupload._File = sx.classes.Component.extend({

        /**
         * @param AjaxFileUpload
         * @param opts
         */
        construct: function(AjaxFileUpload, opts)
        {
            var self = this;

            this.isUploaded = false;
            this.isRemoved  = false;

            if (! (AjaxFileUpload instanceof sx.classes.fileupload.AjaxFileUpload))
            {
                throw new Error('Upload manager not uploaded');
            }

            opts = opts || {};
            this.Uploader = AjaxFileUpload;

            this.applyParentMethod(sx.classes.Component, 'construct', [opts]);
        },

        _init: function()
        {
            this.JWrapper = null;
            this.set('id', this._getRandStr());
        },

        /**
         * @private
         */
        _onDomReady: function()
        {
            var self = this;
        },




        /**
         * Расшифровка состояния
         * @returns {*}
         */
        getStateText: function () {

            var states = this.Uploader.getFileStates();
            return states.get( this.getState(), 'Не определен' );
        },

        /**
         * Код состояния
         * @returns {string}
         */
        getState: function()
        {
            return String(this.get('state', 'undefined'));
        },

        /**
         * @returns {string}
         */
        getName: function()
        {
            return String(this.get('name', 'undefined'));
        },

        /**
         * @returns {string}
         */
        getError: function()
        {
            return this.get('error');
        },

        /**
         * @returns {string}
         */
        getPreview: function()
        {
            return this.get('preview');
        },

        /**
         * @returns {string}
         */
        getValue: function()
        {
            return this.get('value');
        },

        /**
         * @param value
         * @returns {sx.classes.fileupload._File}
         */
        setValue: function(value)
        {
            this.set('value', value);
            this.trigger('changeValue');
            this.trigger('change');
            return this;
        },

        /**
         * @param callback
         */
        onValue: function(callback)
        {
            if (this.getValue())
            {
                callback();
            } else
            {
                this.bind('changeValue', function()
                {
                    callback()
                });
            }
        },

        _initJWrapper: function()
        {
            if (this.JWrapper === null)
            {
                this.JWrapper = this.Uploader.JItemTemplate.clone();
                console.log(this.JWrapper);
                /*this.JWrapper = $('<div>', {'class': 'col-md-3 sx-file'});*/
            }
        },

        /**
         * @returns {*|HTMLElement}
         */
        render: function()
        {
            var self = this;

            this._initJWrapper();

            this.JCaption       = $('<div>', {'class' : 'caption'});
            this.JThumbWrapper  = $('<div>', {'class' : 'thumbnail'});
            this.JFilePrev       = $('<div>', {'class' : 'file-preview'});
            this.JControlls       = $('<div>', {'class' : 'sx-controlls'});
            this.JResult        = $('<div>', {'class' : 'sx-result'});

            this.JControllsRemove = $("<a>", {'class' : 'btn btn-xs sx-remove', 'title' : 'Удалить'}).append(
                $('<i>', {'class' : 'glyphicon glyphicon-remove'})
            );

            this.JControlls.append(
                this.JControllsRemove
            );

            this.JCaption
                .append($('<div>', {'title' : this.getName(), 'class': 'sx-title'}).text(this.getName()))
                .append(this.JResult);

            this.JThumbWrapper.append(this.JFilePrev).append(this.JCaption);

            if (this.getError())
            {
                this.JResult.empty().append(this.getError());
            }

            if (this.getPreview())
            {
                this.JFilePrev.empty().append(this.getPreview());
            }

            if (this.getState() == 'process' || this.getState() == 'queue')
            {
                this.JResult.empty().append(this.getStateText());
                /*this.Blocker.block();*/
            } else if (this.getState() == 'success')
            {
                if (this.getType() == 'image')
                {
                    self.JFilePrev.empty().append(
                        $('<a>', {'href' : self.get('src'), 'target' : '_blank', 'data-pjax' : '0'}).append(
                            $('<img>', {'src' : self.get('src')})
                        )
                    );
                };

                this.JResult.empty().append(this.getResultString());
                /*this.Blocker.unblock();*/
            }

            this.JWrapper
                .removeClass('sx-state-queue')
                .removeClass('sx-state-process')
                .removeClass('sx-state-success')
                .removeClass('sx-state-fail')
                .addClass('sx-state-' + this.getState());

            this.JWrapper.empty().append(this.JControlls).append(this.JThumbWrapper);



            this.JControllsRemove.on('click', function () {
                self.remove()
            });


            return this.JWrapper;
        },

        /**
         * @returns {*}
         */
        getSizeFormated: function()
        {
            if (this.get('sizeFormated'))
            {
                return this.get('sizeFormated');
            } else
            {
                return this.get('size') + " КиБ;";
            }
        },

        getType: function()
        {
            if (this.get('type'))
            {
                var type = this.get('type').split("/");
                return type[0];
            }

            return '';
        },

        /**
         * @returns {string}
         */
        getResultString: function()
        {
            var result = '';
            result = 'Размер: ' + this.getSizeFormated();

            if (this.get('image'))
            {
                var image = this.get('image');
                result = result + " " + image.height + 'x' + image.width + ';';
            }

            return result;
        },

        /**
         * @returns {sx.classes.fileupload._File}
         */
        remove: function()
        {
            this.JWrapper.fadeOut('slow').remove();
            this.isRemoved = true;

            this.Uploader.removeFile(this.get('id'));
            return this;
        },


        /**
         * @returns {string}
         */
        _getRandStr: function()
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
    });

    sx.classes.fileupload.File = sx.classes.fileupload._File.extend();

})(sx, sx.$, sx._);