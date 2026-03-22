<script>
$(function() {
    $elem = $('#customer_rank');
    $('#order_CustomerId').parent().parent().after($elem.html());
    $elem.remove();
});
</script>
