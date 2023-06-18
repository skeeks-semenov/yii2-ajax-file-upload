/*!
* @author Semenov Alexander <semenov@skeeks.com>
* @link http://skeeks.com/
* @copyright 2010 SkeekS (СкикС)
* @date 27.04.2017
*/

// Для начала определим метод XMLHttpRequest.sendAsBinary(),
// если он не определен (Например, для браузера Google Chrome).

if (!XMLHttpRequest.prototype.sendAsBinary) {

    XMLHttpRequest.prototype.sendAsBinary = function (datastr) {
        function byteValue(x) {
            return x.charCodeAt(0) & 0xff;
        }

        var ords = Array.prototype.map.call(datastr, byteValue);
        var ui8a = new Uint8Array(ords);
        this.send(ui8a.buffer);
    }
}

(function (sx, $, _) {
    /**
     * Стандартная ajax загрузка файлов
     */
    sx.classes.fileupload.tools.BigUploadTool = sx.classes.fileupload.tools._Tool.extend({

        run: function () {
            $('#' + this.get('id')).click();
            return false;
        },

        /**
         * Запуск загрузки
         */
        upload: function () {
            var self = this;

            console.log("Начать загрузку Файлов");
            console.log(this.Uploader.Files);

            $.each(this.Uploader.Files, function (index, FileObject) {

                if (FileObject.get("state") == 'process') {
                    console.log("Файл уже в процессе загрузки");
                    return false;
                }

                if (FileObject.get("state") == 'queue') {
                    console.log("Начать загрузку этого файла");

                    self.uploadOneFile(FileObject, 0, true)
                    return false;
                }

            });
        },

        /**
         * Загрузка одного файла по частям
         * @param fileObject
         */
        uploadOneFile: function (FileObject, from, isFirst) {

            var self = this;

            if (FileObject.isRemoved) {
                console.log("Файл удален!");
                return false;
            }

            isFirst = isFirst || false;
            if (from == 0) {
                FileObject.set('state', 'process').render();

                //Файл большой, для начала нужно проверить может загрузка была прервана ранее
                if (FileObject.getSize() > self.getAdditionalLoadingSize() && isFirst) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', self.get("upload_url"), true);
                    xhr.setRequestHeader("Content-Type", "application/x-binary; charset=x-user-defined");
                    xhr.setRequestHeader("Sxuploader-Widget-Id", self.get("id"));
                    // Идентификатор загрузки (чтобы знать на стороне сервера что с чем склеивать)
                    xhr.setRequestHeader("Sxuploader-Upload-Id", FileObject.getId());
                    // Позиция начала в файле
                    xhr.setRequestHeader("Sxuploader-Portion-From", from);
                    // Название файла
                    xhr.setRequestHeader("Sxuploader-File-Name", encodeURI(FileObject.getName()));
                    // Размер целого файла
                    xhr.setRequestHeader("Sxuploader-File-Size", FileObject.getSize());
                    // Размер порции
                    xhr.setRequestHeader("Sxuploader-Portion-Size", portionSize);
                    //Проверка
                    xhr.setRequestHeader("Sxuploader-Check-Resume", 1);

                    xhr.addEventListener("load", function (evt) {
                        // Если сервер не вернул HTTP статус 200, то выведем окно с сообщением сервера.
                        if (evt.target.status != 200) {
                            /*alert(evt.target.responseText);*/
                            FileObject.set('error', evt.target.responseText);
                            FileObject.set('state', 'fail').render();
                            //Грузить след.
                            self.upload();
                            return;
                        }


                        var responseData = JSON.parse(evt.target.responseText);

                        if (responseData.success === true) {
                            //Файл уже загружен
                            if (responseData.data.is_full == 1) {
                                console.log("is_full");
                                //Файл уже загружен, просто переходим к след.
                                FileObject.set('error', '');
                                FileObject.set('state', 'success');

                                FileObject.merge(responseData.data)
                                FileObject.setValue(responseData.data.value);
                                FileObject.render();

                                self.upload(); //загрузка след.

                            } else if (responseData.data.is_loading == 1) {
                                console.log("is_loading");
                                //Продолжаем загрузку файла
                                self.uploadOneFile(FileObject, responseData.data.rootPathSize, false);
                            } else {
                                console.log("new load");
                                //Начинаем загрузку файла
                                self.uploadOneFile(FileObject, 0, false);
                            }

                        } else {
                            //self.uploadOneFile(FileObject, 0, false);

                            FileObject.set('error', responseData.message);
                            FileObject.set('state', 'fail');
                            FileObject.render();
                        }


                    });

                    xhr.addEventListener("error", function (evt) {
                        FileObject.set('error', "There was an error attempting to upload the file.");
                        FileObject.set('state', 'fail').render();

                        //Грузить след.
                        self.upload();
                    });

                    xhr.send();

                    return false;
                }
            }


            // Объект FileReader, в него будем считывать часть загружаемого файла
            var reader = new FileReader();
            // Объект Blob, для частичного считывания файла
            var blob = null;
            // Таймаут для функции setTimeout. С помощью этой функции реализована повторная попытка загрузки
            // по таймауту (что не совсем корректно)
            var xhrHttpTimeout = null;

            //Если размер порции больше размера файла, просто загружаем файл целиком
            var portionSize = self.getPortionSize();
            if (from == 0) {
                if (self.getPortionSize() > FileObject.getSize()) {
                    console.log("Порция больше чем сам файл, грузим целиком за 1 раз.");
                    portionSize = FileObject.getSize();
                } else {
                    console.log("Загрузка файла частями");
                }
            }

            /*
            * Событие срабатывающее после чтения части файла в FileReader
            * @param evt Событие
            */
            reader.onloadend = function (evt) {
                if (evt.target.readyState == FileReader.DONE) {

                    // Создадим объект XMLHttpRequest, установим адрес скрипта для POST
                    // и необходимые заголовки HTTP запроса.
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', self.get("upload_url"), true);
                    xhr.setRequestHeader("Content-Type", "application/x-binary; charset=x-user-defined");

                    xhr.setRequestHeader("Sxuploader-Widget-Id", self.get("id"));

                    // Идентификатор загрузки (чтобы знать на стороне сервера что с чем склеивать)
                    xhr.setRequestHeader("Sxuploader-Upload-Id", FileObject.getId());
                    // Позиция начала в файле
                    xhr.setRequestHeader("Sxuploader-Portion-From", from);
                    // Название файла
                    xhr.setRequestHeader("Sxuploader-File-Name", encodeURI(FileObject.getName()));

                    // Размер целого файла
                    xhr.setRequestHeader("Sxuploader-File-Size", FileObject.getSize());
                    // Размер порции
                    xhr.setRequestHeader("Sxuploader-Portion-Size", portionSize);

                    // Установим таймаут
                    self.xhrHttpTimeout = setTimeout(function () {
                        xhr.abort();
                    }, self.getTimeout());

                    /*
                    * Событие XMLHttpRequest.onProcess. Отрисовка ProgressBar.
                    * @param evt Событие
                    */
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {

                            // Посчитаем количество закаченного в процентах (с точность до 0.1)
                            var percentComplete = Math.round((from + evt.loaded) * 1000 / FileObject.getSize());
                            percentComplete /= 10;

                            FileObject.updateProcess({
                                'total' : FileObject.getSize(),
                                'loaded' : from + evt.loaded,
                                'bitrate' : 0 //todo: доделать
                            });

                            /*console.log(percentComplete);*/

                            // Посчитаем ширину синей полоски ProgressBar
                            /*var width = Math.round((from + evt.loaded) * 300 / FileObject.getSize());

                            // Изменим свойства элементом ProgressBar'а, добавим к нему текст
                            var div1 = document.getElementById('cnuploader_progressbar');
                            var div2 = document.getElementById('cnuploader_progresscomplete');

                            div1.style.display = 'block';
                            div2.style.display = 'block';
                            div2.style.width = width + 'px';
                            if (percentComplete < 30) {
                                div2.textContent = '';
                                div1.textContent = percentComplete + '%';
                            } else {
                                div2.textContent = percentComplete + '%';
                                div1.textContent = '';
                            }*/
                        }

                    }, false);


                    /*
                    * Событие XMLHttpRequest.onLoad. Окончание загрузки порции.
                    * @param evt Событие
                    */
                    xhr.addEventListener("load", function (evt) {

                        // Очистим таймаут
                        clearTimeout(self.xhrHttpTimeout);

                        // Если сервер не вернул HTTP статус 200, то выведем окно с сообщением сервера.
                        if (evt.target.status != 200) {
                            /*alert(evt.target.responseText);*/
                            FileObject.set('error', evt.target.responseText);
                            FileObject.set('state', 'fail').render();
                            //Грузить след.
                            self.upload();
                            return;
                        }

                        // Добавим к текущей позиции размер порции.
                        //that.position += that.options['portion'];
                        var nextPosition = from + self.getPortionSize();

                        // Закачаем следующую порцию, если файл еще не кончился.
                        if (FileObject.getSize() > nextPosition) {
                            self.uploadOneFile(FileObject, nextPosition);
                        } else {

                            var responseData = JSON.parse(evt.target.responseText);

                            if (responseData.success === true) {
                                FileObject.set('error', '');
                                FileObject.set('state', 'success');

                                FileObject.merge(responseData.data)
                                FileObject.setValue(responseData.data.value);
                            } else {
                                FileObject.set('error', responseData.message);
                                FileObject.set('state', 'fail');
                            }

                            FileObject.render();

                            //Грузить след.
                            self.upload();

                            // Если все порции загружены, сообщим об этом серверу. XMLHttpRequest, метод GET,
                            // PHP скрипт тот-же.
                            //var gxhr = new XMLHttpRequest();
                            //gxhr.open('GET', self.get("upload_url") + '?action=done', true);

                            // Установим идентификатор загруки.
                            //xhr.setRequestHeader("Sxuploader-Widget-Id", self.get("id"));
                            //gxhr.setRequestHeader("Sxuploader-Upload-Id", FileObject.getId());
                            //gxhr.setRequestHeader("Sxuploader-Upload-File", encodeURI(FileObject.getName()));

                            /*
                            * Событие XMLHttpRequest.onLoad. Окончание загрузки сообщения об окончании загрузки файла :).
                            * @param evt Событие
                            */
                            /*gxhr.addEventListener("load", function (evt) {

                                // Если сервер не вернул HTTP статус 200, то выведем окно с сообщением сервера.
                                if (evt.target.status != 200) {
                                    alert(evt.target.responseText.toString());
                                    return;
                                }
                                    // Если все нормально, то отправим пользователя дальше. Там может быть сообщение
                                // об успешной загрузке или следующий шаг формы с дополнительным полями.
                                else {
                                    //window.parent.location=that.options['redirect_success'];
                                }
                            }, false);

                            // Отправим HTTP GET запрос
                            gxhr.sendAsBinary('');*/
                        }
                    }, false);

                    /*
                    * Событие XMLHttpRequest.onError. Ошибка при загрузке
                    * @param evt Событие
                    */
                    xhr.addEventListener("error", function (evt) {

                        // Очистим таймаут
                        /*clearTimeout(self.xhrHttpTimeout);

                        // Сообщим серверу об ошибке во время загруке, сервер сможет удалить уже загруженные части.
                        // XMLHttpRequest, метод GET,  PHP скрипт тот-же.
                        var gxhr = new XMLHttpRequest();

                        gxhr.open('GET', self.get("upload_url") + '?action=abort', true);

                        // Установим идентификатор загруки.
                        xhr.setRequestHeader("Sxuploader-Widget-Id", self.get("id"));
                        gxhr.setRequestHeader("Sxuploader-Upload-Id", FileObject.getId());

                        /!*
                        * Событие XMLHttpRequest.onLoad. Окончание загрузки сообщения об ошибке загрузки :).
                        * @param evt Событие
                        *!/
                        gxhr.addEventListener("load", function (evt) {

                            // Если сервер не вернул HTTP статус 200, то выведем окно с сообщением сервера.
                            if (evt.target.status != 200) {
                                alert(evt.target.responseText);
                                return;
                            }
                        }, false);

                        // Отправим HTTP GET запрос
                        gxhr.sendAsBinary('');*/


                        // Отобразим сообщение об ошибке
                        /*if (self.options['message_error'] == undefined) {
                            alert("There was an error attempting to upload the file.");
                        } else {
                            alert(self.options['message_error']);
                        }*/

                        FileObject.set('error', "There was an error attempting to upload the file.");
                        FileObject.set('state', 'fail').render();

                        //Грузить след.
                        self.upload();

                    }, false);

                    /*
                    * Событие XMLHttpRequest.onAbort. Если по какой-то причине передача прервана, повторим попытку.
                    * @param evt Событие
                    */
                    xhr.addEventListener("abort", function (evt) {
                        clearTimeout(self.xhrHttpTimeout);
                        self.uploadOneFile(FileObject, from);
                    }, false);

                    // Отправим порцию методом POST
                    xhr.sendAsBinary(evt.target.result);
                }
            };

            // Считаем порцию в объект Blob. Три условия для трех возможных определений Blob.[.*]slice().
            if (FileObject.get("fileinfo").slice) {

                /*console.log("read slice");
                console.log(from);
                console.log(from + portionSize);*/

                blob = FileObject.get("fileinfo").slice(from, from + portionSize);
            } else {
                if (FileObject.get("fileinfo").webkitSlice) {
                    blob = FileObject.get("fileinfo").webkitSlice(from, from + portionSize);
                } else {
                    if (FileObject.get("fileinfo").mozSlice) {
                        blob = FileObject.get("fileinfo").mozSlice(from, from + portionSize);
                    }
                }
            }

            // Считаем Blob (часть файла) в FileReader
            /*console.log(FileObject.get("fileinfo"));
            console.log(blob);*/
            reader.readAsBinaryString(blob);
        },

        /**
         * @returns {number}
         */
        getPortionSize: function () {
            return Number(this.get("portion", 1048576 * 1));
        },

        /**
         * Дозагрузка больших файлов в случае прерывания
         * @returns {number}
         */
        getAdditionalLoadingSize: function () {
            return Number(this.get("additional_loading", 1048576 * 200));
        },

        /**
         * @returns {number}
         */
        getTimeout: function () {
            return Number(this.get("timeout", 15000));
        },

        _onDomReady: function () {
            /*console.log(this.toArray());*/

            var self = this;
            this.JInput = $('#' + this.get('id'));

            if (self.get("isDragAndDrop")) {
                $(self.get("dropZone")).addClass("dropzone");
                //todo:доработать чтобы вызывалось 1 раз если много виджетов на странице
                //@see https://github.com/blueimp/jQuery-File-Upload/wiki/Drop-zone-effects
                $(document).bind('drop dragover', function (e) {
                    e.preventDefault();
                });

                $(document).bind('dragover', function (e) {
                    var dropZone = $(".dropzone"),
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

                $(self.get("dropZone")).on("drop", function(e) {

                    $.each(e.originalEvent.dataTransfer.files, function (index, file) {
                        jQuery(self.JInput).trigger("fileuploadadd", {
                            "files": [
                                file
                            ]
                        });
                    });

                    //Начать загрузку
                    self.upload();
                });
            }



            //console.log(this.get('uploadfile'));

            jQuery(this.JInput).on('change', function (e) {
                var files = e.target.files;
                var jInput = $(this);

                $.each(files, function (index, file) {
                    jInput.trigger("fileuploadadd", {
                        "files": [
                            file
                        ]
                    });
                });

                //Начать загрузку
                self.upload();
            });


            jQuery(this.JInput).on('fileuploadadd', function (e, data) {

                //console.log("fileuploadadd");

                var FileObject = new sx.classes.fileupload.File(self.Uploader);

                $.each(data.files, function (index, file) {

                    FileObject.set('fileinfo', file);
                    FileObject.set('name', file.name);
                    FileObject.set('state', 'queue');
                    FileObject.set('size', file.size);
                    FileObject.set('mimetype', file.type);
                });

                data.context = FileObject;
                self.Uploader.appendFile(FileObject);
            });

            /*jQuery(this.JInput).on('fileuploadprocessalways', function (e, data) {
                var FileObject = data.context;

                var index = data.index,
                    file = data.files[index];

                console.log(file);

                if (file.preview) {
                    FileObject.set('preview', file.preview);
                }

                if (file.error) {
                    FileObject.set('error', file.error);
                }

                FileObject.render();
            });*/

            /*jQuery(this.JInput).on('fileuploaddone', function (e, data) {
                var FileObject = data.context;

                if (data.result.success === true) {
                    FileObject.set('error', '');
                    FileObject.set('state', 'success');

                    FileObject.merge(data.result.data)
                    FileObject.setValue(data.result.data.value);
                } else {
                    FileObject.set('error', data.result.message);
                    FileObject.set('state', 'fail');
                }

                FileObject.render();
            });

            jQuery(this.JInput).on('fileuploadsend', function (e, data) {
                var FileObject = data.context;

                if (FileObject.isRemoved) {
                    return false;
                }

                FileObject.set('state', 'process');
                FileObject.render();

            });

            jQuery(this.JInput).on('fileuploadfail', function (e, data) {
                var FileObject = data.context;
                FileObject.set('state', 'fail');
                FileObject.set('error', 'File upload failed');
                FileObject.render();
            });

            jQuery(this.JInput).on('fileuploadprogress', function (e, data) {
                var FileObject = data.context;

                FileObject.updateProcess(data);
            });*/

        },

    });

})(sx, sx.$, sx._);