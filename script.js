
define(['jquery'], function($){
    var CustomWidget = function () {
        var self = this, // для доступа к объекту из методов
            system = self.system(), //Данный метод возвращает объект с переменными системы.
            langs = self.langs;  //Объект локализации с данными из файла локализации (папки i18n)

        this.callbacks = {
            settings: function () {
            },
            init: function () {
                return true;
            },
            bind_actions: function () {
                return true;
            },
            render: function () {
                var html = '<div class="download">\
                             <a id="link" display="none" download>Выгрузить\
                             </a></div>';
                self.render_template({
                    caption: {
                        class_name: 'download', //имя класса для обертки разметки
                    },
                    body: html,
                    render: ''
                });

                console.log('first step');

                return true;
            },
            dpSettings: function () {
            },
            advancedSettings: function () {
            },
            destroy: function () {
            },
            contacts: {
                selected: function () {
                }
            },
            leads: {
                selected: function(){
                    console.log('second step');
                    let select = self.list_selected().selected;
                    let id = [];

                    for (let i = 0; i < select.length; i++) {
                        id[i] = select[i]['id'];
                    }
                    var sys = self.system();
                   // console.log(sys);
                    $.ajax({
                        type: 'POST',
                        url: 'http://localhost/index.php',
                        data: {
                            "id": id,
                            "sys": sys
                        },
                        success: function(data) {
                          //  console.log(data);
                           $('#link').attr("href", "http://localhost/"+data);
                        }
                    })
                }
            },
            onSave: function(){
            }
        };
        return this;
    };
    return CustomWidget;
});


