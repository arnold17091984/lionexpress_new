<script>
    $(function() {
        $elem = $('#customerrank');
        $('tbody:eq(0)').append($elem.html());
        $elem.remove();
    });
</script>