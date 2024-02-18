@extends('layouts.app')

@section('title', 'General Dashboard')

@push('style')
    <link rel="stylesheet"
          href="{{ asset('library/owl.carousel/dist/assets/owl.carousel.min.css') }}">
<style>
    .dash-icon {
        font-size: 22px;
        color: #fff;
    }
</style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard</h1>
            </div>


        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraies -->
    <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>
    <script src="{{ asset('library/owl.carousel/dist/owl.carousel.min.js') }}"></script>


@endpush
