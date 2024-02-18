@php use App\Contracts\TransactionType; @endphp
@extends('layouts.app')

@section('title', 'Loan Categories')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            @include('partials.breadcrumb', ['heading' => 'Loan Category',
                                             'route1' => route('dashboards.home'),
                                             'route1Name' => 'Dashboard',
                                             'route2' => '#',
                                             'route2Name' => 'Loan Category',
                                             'route3Name' => 'Lists'])

            <div class="section-body">

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-body">
                                <div class="row">

                                    <div class="col-md-12 d-flex justify-content-end align-items-center">
                                        @can('top-level')
                                            <a href="{{route('settings.create-loan-category')}}" class="btn btn-outline-success">Add New</a>
                                        @endcan
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped mt-3">
                                                <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Action</th>

                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($categories as $category)
                                                    <tr>
                                                        <td>{{$category->name}}</td>
                                                        <td>
                                                            <div class="d-flex">
                                                                @can('top-level')
                                                                    <a href="{{route('settings.edit-loan-category', $category->id)}}"
                                                                       class="btn btn-outline-success">
                                                                        <i class="fa fa-edit"></i>
                                                                    </a>
                                                                @endcan
                                                            </div>

                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                            {{$categories->links()}}
                                        </div>
                                    </div>
                                </div>
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
