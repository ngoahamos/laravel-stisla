<div>




    @if (session()->has('info_message'))

        <script>

            document.addEventListener('DOMContentLoaded', (event) => {
                alertMessage();
            });

            function alertMessage() {
                try {
                    const message = '{{@session('info_message')}}';
                    iziToast.info({
                        title: 'Info',
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
