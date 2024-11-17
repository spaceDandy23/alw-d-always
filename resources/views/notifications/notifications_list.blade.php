@extends('layouts.master')

@section('page_title', 'Notifications')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <table class="table table-striped">
            <thead>
                <form action="{{ route('notifications.filter') }}" method="GET" class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="name" class="form-label">Guardian Name</label>
                    </div>
                    <div class="col">
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter Guardian Name">
                    </div>
                    <div class="col-auto">
                        <label for="start_date" class="form-label">Start Date</label>
                    </div>
                    <div class="col">
                        <input id="start_date" type="date" name="start_date" class="form-control">
                    </div>
                    <div class="col-auto">
                        <label for="end_date" class="form-label">End Date</label>
                    </div>
                    <div class="col">
                        <input id="end_date" type="date" name="end_date" class="form-control">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit">Filter</button>
                    </div>
                </div>
                </form>
                @include('partials.alerts')
                <tr>
                    <th scope="col">Guardian</th>
                    <th scope="col">Message</th>
                    <th scope="col">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notifications as $notif)
                    <tr>
                        <td>{{ $notif->guardian->name }}</td>
                        <td>{{ $notif->message}}</td>
                        <td>{{ $notif->date }}</td>
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
