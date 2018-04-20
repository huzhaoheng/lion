@extends('layouts.app')

@section('content')

    {!! Form::open(['url' => 'meta-queries/' . $query->id . '/submit', 'id' => 'submitMetaQuery', 'method' => 'GET']) !!}

<meta-query-viewer :query="{{ json_encode($query) }}" :formId="submitMetaQuery"></meta-query-viewer>
		{!! Form::close() !!}
@endsection
