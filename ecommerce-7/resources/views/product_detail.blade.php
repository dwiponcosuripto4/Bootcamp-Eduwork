@extends('template.layouts')

@section('title', 'Product Detail Page')
@section('content')
    @push('js')
        <script>
            alert('selamat datang');
        </script>
    @endpush
    <h1>Product Detail Page</h1>
    <p>This is the product detail page.</p>
    @push('css')
        <style>
            h1 {
                color: red;
            }
        </style>
    @endpush
@endsection
