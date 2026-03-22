<script>
    $(function() {
        $elem = $('#search_customer_rank');
        $('#searchDetail').prepend($elem.html());
        $elem.remove();
        $('table tr').each(function(i) {
            if (i != 0) {
                $elem = $('#c' + i);
                if($elem.length !== 0){
                    $('td:eq(1)', this).after('<td class="align-middle">'+ $elem.text() +'</td>');
                    $elem.remove();
                }
            } else {
                $elem = $('#customer_rank_header');
                if($elem.length !== 0){
                    $('th:eq(1)', this).after('<th class="border-top-0 pt-2 pb-3">' + $elem.text() + '</th>');
                    $elem.remove();
                }
            }
        });
    });
</script>