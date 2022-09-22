@extends('layouts.app')
@section('content')
@php
    use App\Constants\DataConstant;
@endphp
<div class="main-content">
    @include('components.import.add')
</div>
@endsection