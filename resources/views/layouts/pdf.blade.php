<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="{!! config('app.url') . '/css/' . 'pdf.css' !!}">
    <title></title>
    <style type="text/css">
        tr { page-break-inside: avoid }
        table {
            font-size: 13px;
        }
        div.page
        {
            page-break-after: always;
            page-break-inside: avoid;
        }
        .img-responsive {
            display: block;
            max-width: 100%;
            height: auto;
        }
        .d-color {
            color: #e31e24;
        }
        @yield('style')
    </style>
</head>
<body style="background: white">
<div class="container">
    @yield('container')
</div>

</body>
</html>
