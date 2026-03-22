<script>
    $(function() {
        $elem = $('#customer_rank');
        $('.card-body:eq(0)', this).prepend($elem.html());
        $elem.remove();

        $elem = $('#customer_rank_info');
        $('.card.rounded.border-0.mb-4:eq(0)', this).after($elem.html());
        $elem.remove();
    });
</script>
