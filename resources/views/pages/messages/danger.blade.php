<div>
    @if ($errors->any())



        <script>

            document.addEventListener('DOMContentLoaded', (event) => {
                alertDanger();
            });

            function alertDanger() {

             try {
                 const message = '{{implode(', ', $errors->all())}}';
                 iziToast.error({
                     title: 'Error',
                     message,
                     position: 'topRight'
                 });
             }catch (e) {
                 console.log(e);
             }
            }

        </script>


    @endif
</div>
