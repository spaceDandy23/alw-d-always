@extends('layouts.master')

@section('page_title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-center">
                <h3>Login</h3>
            </div>
            <div class="card-body">
                @include('partials.alerts')
                <form action="{{route('login')}}" method="POST">
                    @csrf
                        <label for="email" class="form-label">Email address</label>
                        <input type="text" class="form-control" id="email" name="email">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control mb-2" id="password" name="password">
                        <!-- <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label> -->
                        <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <!-- <div class="mt-3 text-center">
                    <a href="">Forgot Your Password?</a>
                </div> -->
            </div>
        </div>
    </div>
</div>
@endsection
