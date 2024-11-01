/****************************************************************
 * @author      : Jekferson Diaz
 * @name        : XChange
 * @description  : Objeto de Dominio XChange
 * @copyright (c)  Xchange
 *****************************************************************/
XChange = (function () {
    'use strict';
    /****************************************************************
     * Declaracion de variables
     *****************************************************************/
    var $data = {};
    var $onAuthorize = function (response) {
        //Custom Ajax function to process Payment via WC-AJAX
        console.log(response);
        jQuery.ajax({
            url: "https://mdjc7112pd.execute-api.us-east-1.amazonaws.com/v1/xchange",
            type: "POST",
            data: jQuery(".woocommerce-checkout").serialize(),
            success: function (respuesta) {
                if (respuesta.result == "failure") {
                    jQuery(".woocommerce-message").remove();
                    jQuery(".woocommerce-NoticeGroup-checkout").append(respuesta.messages);
                } else if (respuesta.result == "success") {
                    parent.location.href = respuesta.redirect;
                }
            },
            error: function () {
                console.log("No se ha podido obtener la información");
            }
        });
    };
    /****************************************************************
     * Declaracion de metodos
     *****************************************************************/
    var init,
        Reload,
        /**
         * @name: init
         * @description: Método de inicialización
         */
        init = function (data) {
            try {

                $data = data;
                console.log("data recibe", $data);

                var object = {
                    "data": $data,
                    "onAuthorizer": $onAuthorize
                };

                Data.init(object);

            } catch (ex) {
                console.warn(ex);
            }
        },
        /**
         * @name: init
         * @description: Método de inicialización
         */
        Reload = function (data) {
            try {

                Data.Reload(data);

            } catch (ex) {
                console.warn(ex);
            }
        },

       

    return {
        init: init,
        Reload: Reload,
    };

})();