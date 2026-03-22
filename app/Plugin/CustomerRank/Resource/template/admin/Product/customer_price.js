{% if has_class == false %}
    <script>
        $(function() {
            $('div.row').each(function(i) {
                if($(this).text().match(/{{'admin.product.sale_price'|trans}}/)){
                    $elem = $('#customer_price');
                    $(this).after($elem.html());
                    $elem.remove();
                }
            });
        });
    </script>
{% endif %}