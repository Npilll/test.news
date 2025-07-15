(function (window){
    'use strict';
    if (window.JSAjaxComponent) {
        return;
    }

    window.JSAjaxComponent = function (arParams)
    {
        if (typeof arParams === 'object')
        {
            this.params = arParams;
        }
        this.errorCode = 0;
        this.data = this.params.PARAMS;
        this.ajaxPath = this.params.AJAX_PATH;
        this.filePath = this.params.FILE_PATH;
        this.areaID = this.params.AREA_ID;


        if (this.errorCode === 0)
        {
            BX.ready(BX.delegate(this.init,this));
        }
    };

    window.JSAjaxComponent.prototype = {
        init: function()
        {
            var data = {
                'FILE_PATH': this.filePath,
                'PARAMS': this.data
            };
            var stringParams = jQuery.param(data);
            $.ajax({
                url: this.ajaxPath,
                type: 'POST',
                dataType: 'html',
                data: data,
            })
                .done((resp) => {
                    let regex = /\/ajax.component\/ajax.php\?/g;
                    resp = resp.replaceAll(regex,'/ajax.component/ajax.php?'+stringParams+'&');
                    $('#'+this.areaID).replaceWith(resp);
                })
                .fail((error) => {
                    console.log(error);
                });

        },
    };
})(window);