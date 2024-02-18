<div class="row mt-3">
    <div class="col-4" style="text-align: center">
        @if($company)
            <img src="{{ $company->raw_logo ?  $company->logo : image_placeholder(150, null, 'LOGO') }}" width="150px" height="150px">
        @endif
    </div>
    <div class="col-8 pull-1">
        @if($company)
            <h3 class="font-weight-bold text-uppercase">{{$company ? $company->name : ''}}</h3>
            <span><b>Address:</b> {{$company ? $company->address: ''}}</span><br>
            @if($company->email or $company->website)
                <span>
                    @if($company->email)
                        <b>E-mail:</b> {{ $company->email }} |
                    @endif
                    @if($company->website)
                        <b>Website:</b> {{$company->website}}
                    @endif
                </span><br>
            @endif

            @if($company and $company->telephone)
                <span><b>Tel:</b> {{$company->telephone}}</span>
            @endif
        @endif
    </div>
</div>
