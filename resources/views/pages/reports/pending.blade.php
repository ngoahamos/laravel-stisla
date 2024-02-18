@php use App\Contracts\TransactionType; @endphp
@extends('layouts.app')

@section('title', 'Pending Approval')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            @include('partials.breadcrumb', ['heading' => 'Pending Approval',
                                             'route1' => route('dashboards.home'),
                                             'route1Name' => 'Dashboard',
                                             'route2' => '#',
                                             'route2Name' => 'Loans',
                                             'route3Name' => 'Pending Approval'])

            <div class="section-body">

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <span class="text-dark">Principal</span><br />
                                        <span class="text-muted">GHS {{$principal}}</span>
                                    </div>


                                    <div class="col-md-3">
                                        <span class="text-dark">Amount</span><br />
                                        <span class="text-muted">GHS {{pretty_amount($amount)}}</span>
                                    </div>

                                    <div class="col-md-3">
                                        <span class="text-dark">Interest</span><br />
                                        <span class="text-muted">GHS {{pretty_amount($interest)}}</span>
                                    </div>

                                    <div class="col-md-3">
                                        <span class="text-dark">Repayment</span><br />
                                        <span class="text-muted">GHS {{pretty_amount($repayment)}}</span>
                                    </div>

                                    <div class="col-md-3">
                                        <span class="text-dark">Balance</span><br />
                                        <span class="text-muted">GHS {{pretty_amount($balance)}}</span>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped mt-3">
                                                <thead>
                                                <tr>
                                                    <th>Loan Acc. #</th>
                                                    <th>Name</th>
                                                    <th>Category</th>
                                                    <th>Principal</th>
                                                    <th>Interest</th>
                                                    <th>Amount</th>
                                                    <th>Repayment</th>
                                                    <th>Balance</th>
                                                    <th>Actions</th>


                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($loans as $loan)
                                                    <tr>
                                                        <td>{{$loan->loanAccount ? $loan->loanAccount->account_number : ''}}</td>
                                                        <td>{{$loan->name}}</td>
                                                        <td>{{$loan->category ? $loan->category->name : ''}}</td>
                                                        <td>{{$loan->principal}}</td>
                                                        <td>{{pretty_amount($loan->interestAmount)}}</td>
                                                        <td>{{pretty_amount($loan->amount)}}</td>
                                                        <td>{{pretty_amount(get_repayment($loan))}}</td>
                                                        <td>{{$loan->balance ? pretty_amount($loan->balance->amount) : 0.00}}</td>

                                                        <td>
                                                            <div class="d-flex">
                                                                @can('top-level')
                                                                    <a href="{{route('settings.edit-loan-category', $loan->id)}}"
                                                                       class="btn btn-outline-success">
                                                                        <i class="fa fa-eye"></i>
                                                                    </a>
                                                                @endcan
                                                            </div>

                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                            {{$loans->links()}}
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
