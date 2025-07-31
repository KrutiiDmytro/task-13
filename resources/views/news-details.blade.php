@extends('layouts.app')

@section('content_home')
    <h1>{{ $data['title'] }}</h1>
    <p>Category: {!! $category['category'] !!}</p>
@endsection

