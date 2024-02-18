<div>
    @if (session()->has('error_message'))

        <script>

            document.addEventListener('DOMContentLoaded', (event) => {
                alertMessage();
            });

            function alertMessage() {
                try {
                    const message = '{{@session('error_message')}}';
                    iziToast.error({
                        title: 'Error!',
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
