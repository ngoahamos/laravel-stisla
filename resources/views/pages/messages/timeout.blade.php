@if(auth()->check())
    <script type="text/javascript">
        let idleTime = 0;
        $(document).ready(function () {
            // Increment the idle time counter every minute.
            let idleInterval = setInterval(timerIncrement, 60000); // 1 minute  60000

            // Zero the idle timer on mouse movement.
            $(this).mousemove(function (e) {
                idleTime = 0;
            });
            $(this).keypress(function (e) {
                idleTime = 0;
            });
        });

        function timerIncrement() {

            idleTime = idleTime + 1;
            if (idleTime > 10) {
                try {
                    const message = 'System about to shutdown for inactivity.';
                    iziToast.warning({
                        title: 'Warning',
                        message,
                        position: 'topRight'
                    });
                }catch (e) {
                    console.log(e);
                }
            }
            if (idleTime > 15) { // 15 minutes
               const url = {{\Illuminate\Support\Js::from(route('logout'))}}
              $.get(url, function (){

                  window.location.reload();
               });

            }
        }
    </script>
@endif
