@extends('layouts.master')

@section('page_title', 'RFID Logs')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="p-4">
            <form action="{{ route('logs.filter') }}" method="GET" class="mb-4">
            @include('partials.search_with_date')
            <div class="row g-3 align-items-center mt-2">
                <div class="col-auto">
                    <label for="rfid_tag" class="form-label">RFID Tag</label>
                </div>
                <div class="col-3">
                    <input type="text" name="rfid_tag" id="rfid_tag" class="form-control" placeholder="Enter RFID Tag">
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" type="submit">Filter</button>
            </div>
            </form>
        </div>
        <table class="table table-striped">
            <thead>
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
