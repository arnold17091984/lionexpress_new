<script>
    $(function() {
        var index = 0;
        var start = 8;
        $('th').each(function(i) {
            if($(this).text().match(/{{'admin.product.sale_price'|trans}}/)){
                start = i;
            }
        });
        $('table tr').each(function(i) {
            if (i != 0) {
                index =  start;
                for(j=1;j<={{ CustomerRanks|length }};j++){
                    $elem = $('#customer_price_' + i + '_' + j);
                    $('td:eq('+index+')', this).after('<td class="align-middle">' + $elem.html() + '</td>');
                    $elem.remove();
                    index++;
                }
            } else {
                index = start;
                for(j=1;j<={{ CustomerRanks|length }};j++){
                    $elem = $('#customer_price_th_' + j);
                    $('th:eq('+index+')').after('<th class="pt-2 pb-2">' + $elem.text() + '</th>');
                    $elem.remove();
                    index++;
                }
            }
        });

        // 1行目をコピーボタン
        $('#copy').click(function() {
            {% for CustomerRank in CustomerRanks %}
                var price = $('#product_class_matrix_product_classes_0_customer_price_{{ CustomerRank.id }}').val();
                $('input[id$=_customer_price_{{ CustomerRank.id }}]').val(price);
            {% endfor %}
        });
    });
</script>



