@extends('layouts.master')

@section('page_title', 'Attendance Filter')

@section('content')
<div class="row justify-content-center"> 
    <div class="col">
        <div class="text-center">
            <h3>Filter</h3>
        </div>
        @include('partials.alerts')
        <div class="p-4">
            <form action="{{ route('attendances.reports.filter') }}" method="GET" class="mb-4">
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
    </div>
    @if(isset($studentsTotalAbsents))
        <table class="table table-bordered mt-3">
            
            <thead>
                <h3>Total Absences for filtered days ({{ $startDateEndDate }})</h3>
                <tr>
                    <th>Student Name</th>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>School Year</th>
                    <th>Total Absences</th>
                </tr>
            </thead>
            <tbody>
            <tbody>
                @foreach($studentsTotalAbsents as $student)
                    <tr>
                        <td>{{ $student['student']->name }}</td>
                        <td>{{ $student['student']->grade }}</td>
                        <td>{{ $student['student']->section }}</td>
                        <td>{{ $student['student']->schoolYear->year }}</td>
                        <td>{{ $student['total_absences'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    @if(isset($attendanceRecords))
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student Name</th>
                    <th>Status (Morning)</th>
                    <th>Status (Lunch)</th>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>Total Absences</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceRecords as $record)
                    <tr>
                        <td>{{ $record->date }}</td>
                        <td>{{ $record->student->name }}</td>
                        <td>{{ $record->status_morning }}</td>
                        <td>{{ $record->status_lunch }}</td>
                        <td>{{ $record->student->grade }}</td>
                        <td>{{ $record->student->section }}</td>
                        <td>{{ $record->total_absences }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="mt-3">No records found for the selected filters.</p>
    @endif
</div>


@endsection
