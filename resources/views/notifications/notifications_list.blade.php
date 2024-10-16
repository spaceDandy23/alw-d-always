@extends('layouts.master')

@section('page_title', 'RFID Logs')

@section('content')

<div class="row justify-content-center">
    <div class="col-10">
        <table class="table table-striped">
            <thead>
                @include('partials.alerts')
                <tr>
                    <th scope="col">Guardian</th>
                    <th scope="col">Ward</th>
                    <th scope="col">Message</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notifications as $notif)
                    <tr>
                        <td>{{ $notif->guardian->name }}</td>
                        <td>{{ $notif->student->name }}</td>
                        <td>{{ $notif->message}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $notifications->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
