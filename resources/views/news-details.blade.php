@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>{{ $data['title'] }}</h1>
    <p>Категория: {{ $category['category'] }}</p>
</div>
@endsection
