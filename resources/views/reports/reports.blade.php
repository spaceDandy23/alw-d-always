@extends('layouts.master')

@section('page_title', 'Attendance Filter')

@section('content')
<div class="row justify-content-center"> 
    <div class="card col-6 px-0">
        <div class="card-header text-center">
            <h3>Attendance Filter</h3>
        </div>
        <div class="card-body">
            @include('partials.alerts')
            <div class="p-4 mt-4">
                <h5>Filter Attendance Records</h5>
                <form action="{{ route('attendances.filter') }}" method="GET" class="mb-4">
                    <div class="mb-4">
                        <h6>Start Date</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_day" class="form-label">Day</label>
                                <select class="form-select" id="start_day" name="start_day">
                                    <option value="">-- Select Day --</option>
                                    @for ($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="start_month" class="form-label">Month</label>
                                <select class="form-select" id="start_month" name="start_month">
                                    <option value="">-- Select Month --</option>
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- End Date Section -->
                    <div class="mb-4">
                        <h6>End Date</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="end_day" class="form-label">Day</label>
                                <select class="form-select" id="end_day" name="end_day">
                                    <option value="">-- Select Day --</option>
                                    @for ($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_month" class="form-label">Month</label>
                                <select class="form-select" id="end_month" name="end_month">
                                    <option value="">-- Select Month --</option>
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Grade Section -->
                    <div class="mb-4">
                        <label for="grade" class="form-label">Grade</label>
                        <select class="form-select" id="grade" name="grade">
                            <option value="">-- Select Grade --</option>
                            <option value="7">Grade 7</option>
                            <option value="8">Grade 8</option>
                            <option value="9">Grade 9</option>
                            <option value="10">Grade 10</option>
                        </select>
                    </div>

                    <!-- Section Section -->
                    <div class="mb-4">
                        <label for="section" class="form-label">Section</label>
                        <select class="form-select" id="section" name="section">
                            <option value="">-- Select Section --</option>
                            <option value="1">Section 1</option>
                            <option value="2">Section 2</option>
                            <option value="3">Section 3</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>
    </div>
    @if(isset($attendanceRecords))
    <h3>Filtered Attendance Records</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Date</th>
                <th>Student Name</th>
                <th>Status (Morning)</th>
                <th>Status (Lunch)</th>
                <th>Grade</th>
                <th>Section</th>
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
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p class="mt-3">No records found for the selected filters.</p>
    @endif
</div>
@endsection
