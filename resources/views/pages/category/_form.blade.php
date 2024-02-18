@if(isset($category))
    {!! Form::model($category, ['route' => ['settings.update-loan-category', $category->id], 'method' => 'put']) !!}
@else
    {!! Form::open(['url' =>  route('settings.store-loan-category'), 'method' =>  'post']) !!}
@endif
{!! Form::token() !!}
@if(!isset($category))
    {!! Form::hidden('user_id', auth()->user()->id) !!}
@endif
{!! Form::hidden('company_id', auth()->user()->company_id) !!}
<div class="form-group">
    {{ Form::label('name', 'Name') }}
    {{ Form::text('name', null, ['class' => 'form-control' ]) }}
    @error('name')
    <span class="text-danger">{{$message}}</span>
    @enderror()

</div>


<div class="form-group">
    <button type="submit" class="btn btn-outline-primary"
            id="form-submit">{{isset($category) ? 'Update' : 'Save'}}</button>

</div>

{!! Form::close() !!}

@push('scripts')


@endpush
