@php use App\Contracts\TransactionType; @endphp
@extends('layouts.app')

@section('title', 'New Loan Category')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            @include('partials.breadcrumb', ['heading' => 'New Loan Category',
                                             'route1' => route('dashboards.home'),
                                             'route1Name' => 'Dashboard',
                                             'route2' => route('settings.loan-categories'),
                                             'route2Name' => 'Loan Category',
                                             'route3Name' => 'New'])

            <div class="section-body">

                <div class="row d-flex justify-content-center">
                    <div class="col-md-6">
                        <div class="card">

                            <div class="card-body">
                                @include('pages.category._form')
                            </div>
                        </div>
                    </div>
                </div>


            </div>


        </section>

    </div>
@endsection

@push('scripts')

@endpush
