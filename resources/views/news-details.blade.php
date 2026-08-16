@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>{{ $data['title'] }}</h1>
    <p>Category: {{ $category['category'] }}</p>
</div>
@endsection
