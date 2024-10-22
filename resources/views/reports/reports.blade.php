@extends('layouts.master')

@section('page_title', 'Attendance Filter')

@section('content')
<div class="row justify-content-center"> 
    <div class="card col-6 px-0">
        <div class="card-header text-center">
            <h3>Filter</h3>
        </div>
        <div class="card-body">
            @include('partials.alerts')
            <div class="p-4">
                <form action="{{ route('attendances.filter') }}" method="GET" class="mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6 mb-3">
                            <label for="start_month" class="form-label">Start Month</label>
                            <select class="form-select" id="start_month" name="start_month">
                                <option value="">-- Select Month --</option>
                                @for ($month = 1; $month <= 12; $month++)
                                    <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="start_day" class="form-label">Start Day</label>
                            <select class="form-select" id="start_day" name="start_day">
                                <option value="">-- Select Day --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_month" class="form-label">End Month</label>
                            <select class="form-select" id="end_month" name="end_month">
                                <option value="">-- Select Month --</option>
                                @for ($month = 1; $month <= 12; $month++)
                                    <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_day" class="form-label">End Day</label>
                            <select class="form-select" id="end_day" name="end_day">
                                <option value="">-- Select Day --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="grade" class="form-label">Grade</label>
                            <select class="form-select" id="grade" name="grade">
                                <option value="">-- Select Grade --</option>
                                <option value="7">Grade 7</option>
                                <option value="8">Grade 8</option>
                                <option value="9">Grade 9</option>
                                <option value="10">Grade 10</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="section" class="form-label">Section</label>
                            <select class="form-select" id="section" name="section">
                                <option value="">-- Select Section --</option>
                                <option value="1">Section 1</option>
                                <option value="2">Section 2</option>
                                <option value="3">Section 3</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="school_year" class="form-label">School Year</label>
                            <select class="form-select" id="school_year" name="school_year">
                                <option value="">-- Select School Year --</option>
                                @foreach ($schoolYears as $schoolYear )
                                    <option value={{ $schoolYear->id }}>{{ $schoolYear->year }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
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
<script src="{{ asset('js/setDaysForMonths.js') }}" type="module"></script>

@endsection
