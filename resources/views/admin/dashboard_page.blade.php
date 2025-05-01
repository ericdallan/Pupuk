@extends('layouts/app')
@section('content')
@section('title', 'Dashboard')
<h2>Dashboard
</h2>
@if (session('success'))
<div id="success-message" class="alert alert-success" style="cursor: pointer;">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div id="error-message" class="alert alert-danger" style="cursor: pointer;">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@elseif (session('message'))
<div id="error-message" class="alert alert-danger" style="cursor: pointer;">
    {{ session('message') }}
</div>
@endif
@endsection