@extends('layouts.master')

@section('page_title', 'RFID Logs')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="p-4">
            <form action="{{ route('logs.filter') }}" method="GET" class="mb-4">
            @include('partials.search_with_date')
            </form>
        </div>
        <table class="table table-striped">
            <thead>
                @include('partials.alerts')
                <tr>
                    <th scope="col">Student Name</th>
                    <th scope="col">Grade</th>
                    <th scope="col">Section</th>
                    <th scope="col">Checked in at</th>
                    <th scope="col">Checked out at</th>
                    <th scope="col">School Year</th>
                    <th scope="col">RFID Tag</th>
                    <th scope="col">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rfidLogs as $log)
                    <tr>
                        <td>{{ $log->student->name }}</td>
                        <td>{{ $log->student->grade }}</td>
                        <td>{{ $log->student->section }}</td>
                        <td>{{ $log->check_in }}</td>
                        <td>{{ $log->check_out }}</td>
                        <td>{{ $log->student->SchoolYear->year }}</td>
                        <td>{{ $log->tag->rfid_tag }}</td>
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
