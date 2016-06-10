(function ($) {
    Drupal.behaviors.moduleNameCopyFieldValue = {
        attach: function (context, settings) {

            var i = 0, importBruto = $("input#edit-field-total-importe-bruto-fact-und-0-value"),
                descuentoFactura = $("input#edit-field-descuento-factura-und-0-value"),
                imponibleFactura = $("input#edit-field-base-imponible-factura-und-0-value"),
                calcFactura = $("input#edit-field-irpf-calc-factura-und-0-value"),
                impuestoFactura = $("input#edit-field-total-impuesto-factura-und-0-value"),
                totalFactura = $("input#edit-field-total-factura-und-0-value"),
                emailEnviado = $("input#edit-field-fecha-de-email-enviado-und-0-value-date");


            importBruto.attr('readonly', true).css("background-color", "#F2F2F2");
            descuentoFactura.attr('readonly', true).css("background-color", "#F2F2F2");
            imponibleFactura.attr('readonly', true).css("background-color", "#F2F2F2");
            calcFactura.attr('readonly', true).css("background-color", "#F2F2F2");
            impuestoFactura.attr('readonly', true).css("background-color", "#F2F2F2");
            totalFactura.attr('readonly', true).css("background-color", "#F2F2F2");
            emailEnviado.attr('readonly', true).css("background-color", "#F2F2F2");

            while ($("input[id^='edit-field-productos-und-" + i + "-field-base-imponible-und-']").length > 0) {
                $("input[id^='edit-field-productos-und-" + i + "-field-base-imponible-und-']").add($("input[id^='edit-field-productos-und-"+i+"-field-total-producto-und-']")).attr('readonly', true)
                    .css("background-color", "#F2F2F2").attr('readonly', true).css("background-color", "#F2F2F2");
                i++;
            }

            $('input:text', "#field-productos-values").add($("input#edit-field-irpf-porc-factura-und-0-value")).live("keyup",function () { // run anytime the value changes

                $(this).css("background-color", "white");
                if ((!(/^\d+$/.test($(this).val()) || /^\d+,\d+$/.test($(this).val())) && !/.*nombre-producto.*/.test($(this).attr("id")))
                ) {
                    $(this).css("background-color", "red");
                }

                if (!/^\d+$/.test($(this).val()) && /.*cantidad-producto.*/.test($(this).attr("id"))) {
                    $(this).css("background-color", "red");
                }

                if (/\d+,$/.test($(this).val())) {
                    return;
                }

                var i = 0, cantidad = 0, precio = 0, dto = 0, total_pre = 0,
                    total_dto = 0, total_bi = 0, impuesto = 0, impuesto_100 = 0,
                    total_imp = 0, total = 0, total_bi_f = 0, total_f = 0,
                    total_cantidad = 0, total_descuento = 0, total_base_imponible = 0, total_descuento_string = "",
                    total_base_imponiblef = "", irpf_porcent = 0, irpf_porcent_100 = 0, irpf_calc = 0,
                    irpf_calcf = "", total_impuesto = 0, total_impuestof = "", total_factura_productos = 0, total_factura_productosf = "",
                    total_cantidad_string = "";

                while ($("input[id^='edit-field-productos-und-" + i + "-field-base-imponible-und-']").length > 0) {
                    cantidad = parseFloat($("input[id^='edit-field-productos-und-" + i + "-field-cantidad-producto-und-']").val().replace(/,/g, '.')) || 0;

                    precio = parseFloat($("input[id^='edit-field-productos-und-" + i + "-field-precio-producto-und-']").val().replace(/,/g, ".")) || 0; // get value of field
                    dto = parseFloat($("input[id^='edit-field-productos-und-" + i + "-field-dto-producto-und-']").val().replace(/,/g, ".")) || 0; // convert it to a float


                    total_pre = cantidad * precio; // add them together
                    total_dto = total_pre * dto / 100;
                    total_bi = total_pre - total_dto;


                    impuesto = parseFloat($("input[id^='edit-field-productos-und-" + i + "-field-impuesto-producto-und-']").val().replace(/,/g, ".")) || 0;
                    impuesto_100 = impuesto / 100;
                    total_imp = total_bi * impuesto_100;
                    total = total_bi + total_imp;


                    total_bi_f = parseFloat(total_bi).toFixed(2).replace(".", ",");
                    total_f = parseFloat(total).toFixed(2).replace(".", ",");

                    $("input[id^='edit-field-productos-und-" + i + "-field-base-imponible-und-']").val(total_bi_f); // output it
                    $("input[id^='edit-field-productos-und-" + i + "-field-total-producto-und-']").val(total_f);

                    total_cantidad += total_pre;
                    total_descuento += total_dto;
                    total_base_imponible += total_bi;
                    total_impuesto += total_imp;
                    total_factura_productos += total;
                    i++;

                }

                total_descuento_string = parseFloat(total_descuento).toFixed(2).replace(".", ",");
                total_cantidad_string = parseFloat(total_cantidad).toFixed(2).replace(".", ",");
                total_base_imponiblef = parseFloat(total_base_imponible).toFixed(2).replace(".", ",");

                irpf_porcent = parseFloat($("input#edit-field-irpf-porc-factura-und-0-value").val().replace(/,/g, ".")) || 0;
                irpf_porcent_100 = irpf_porcent / 100;
                irpf_calc = irpf_porcent_100 * total_base_imponible;
                irpf_calcf = parseFloat(irpf_calc).toFixed(2).replace(".", ",");
                total_impuestof = parseFloat(total_impuesto).toFixed(2).replace(".", ",");
                total_factura_productosf = parseFloat(total_factura_productos).toFixed(2).replace(".", ",");

                $("input#edit-field-total-importe-bruto-fact-und-0-value").val(total_cantidad_string);
                $("input#edit-field-descuento-factura-und-0-value").val(total_descuento_string);
                $("input#edit-field-base-imponible-factura-und-0-value").val(total_base_imponiblef);
                $("input#edit-field-irpf-calc-factura-und-0-value").val(irpf_calcf);
                $("input#edit-field-total-impuesto-factura-und-0-value").val(total_impuestof);
                $("input#edit-field-total-factura-und-0-value").val(total_factura_productosf);
            });

        }
    };
})(jQuery);