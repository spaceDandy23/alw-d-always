@extends('layouts.master')

@section('page_title', 'RFID Logs')

@section('content')

<div class="row justify-content-center">
    <div class="col-10">
        <div class="d-flex justify-content-center mb-2">
            <!-- Optional button for exporting logs add filtering -->
        </div>
        <table class="table table-striped">
            <thead>
                @include('partials.alerts')
                <tr>
                    <th scope="col">Student ID</th>
                    <th scope="col">Check-in Time</th>
                    <th scope="col">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rfidLogs as $log)
                    <tr>
                        <td>{{ $log->student->name }}</td>
                        <td>{{ $log->check_in_time }}</td>
                        <td>{{ $log->date}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $rfidLogs->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
