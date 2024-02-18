<div>




    @if (session()->has('success_message'))



        <script>

            document.addEventListener('DOMContentLoaded', (event) => {
                alertMessage();
            });

            function alertMessage() {
                try {
                    const message = '{{@session('success_message')}}';
                    iziToast.success({
                        title: 'Success',
                        message,
                        position: 'topRight'
                    });
                }catch (e) {
                    console.log(e);
                }
            }

        </script>


    @endif

        @if (session()->has('status'))



            <script>

                document.addEventListener('DOMContentLoaded', (event) => {
                    alertStatus();
                });

                function alertStatus() {
                    try {
                        const message = '{{session('status')}}';
                        iziToast.success({
                            title: 'Success',
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
