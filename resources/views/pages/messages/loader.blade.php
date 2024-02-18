<div>
    <script>
        $(window).on('beforeunload', function(){

            $("#form-submit").addClass('btn-progress')
            $(".form-submit").addClass('btn-progress')

        });


        function confirmDel(e) {
            if(!confirm('Are you sure?')) {
                e.preventDefault();
            }
        }


    </script>
</div>
