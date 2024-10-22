@extends('layouts.master')

@section('page_title', 'RFID Logs')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="p-4">
            <form action="{{ route('logs.filter') }}" method="GET" class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="name" class="form-label">Student Name</label>
                    </div>
                    <div class="col">
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter Student Name">
                    </div>
                    <div class="col-auto">
                        <label for="grade" class="form-label">Grade</label>
                    </div>
                    <div class="col">
                        <select class="form-select" id="grade" name="grade">
                            <option value="">-- Select Grade --</option>
                            @for($i = 7; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-auto">
                        <label for="filter_section" class="form-label">Section</label>                                              
                    </div>
                    <div class="col">
                        <select id="filter_section" class="form-select" name="section">
                            <option value="">-- Select Section --</option>
                            @for($i = 1; $i <= 3; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>  
                    </div>
                </div>
                <div class="row g-3 mt-2 align-items-center">
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
        </div>
        <table class="table table-striped">
            <thead>
                @include('partials.alerts')
                <tr>
                    <th scope="col">Student Name</th>
                    <th scope="col">Grade</th>
                    <th scope="col">Section</th>
                    <th scope="col">Check-in Time</th>
                    <th scope="col">School Year</th>
                    <th scope="col">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rfidLogs as $log)
                    <tr>
                        <td>{{ $log->student->name }}</td>
                        <td>{{ $log->student->grade }}</td>
                        <td>{{ $log->student->section }}</td>
                        <td>{{ $log->check_in_time }}</td>
                        <td>{{ $log->student->SchoolYear->year }}</td>
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
<script type="module" src="{{ asset('js/setDaysForMonths.js') }}"></script> 
@endsection
