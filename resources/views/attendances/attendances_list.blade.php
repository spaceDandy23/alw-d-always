@extends('layouts.master')

@section('page_title', 'Attendance Report')

@section('content')
<div class="row justify-content-center">
    <div class="col">
        <div class="p-4">
            <!-- Search Form -->
            <form action="{{ route('attendances.reports.filter') }}" method="GET" class="mb-4">
                @include('partials.search_with_date')
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
        <div>

  
   

        </div>
        @if(isset($attendances))
        <!-- Summary Table -->
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title text-center">
                    Attendance Summary for {{ $totalNumbers['startDate'] ?? '' }} - {{ $totalNumbers['endDate'] ?? '' }}
                </h4>
                <table class="table table-bordered text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>Total Absent</th>
                            <th>Total Present</th>
                            <th>Total Number Of Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $totalNumbers['overallAbsent'] ?? '0' }}</td>
                            <td>{{ $totalNumbers['overallPresent'] ?? '0' }}</td>
                            <td>{{ $totalNumbers['totalStudents'] ?? '0' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Attendance Report Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th colspan="8" class="text-center">Detailed Attendance Report</th>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <th>Section</th>
                        <th>Total Number Of Days Absent</th>
                        <th>Total Number Of Days Present</th>
                        <th>School Year</th>
                        <th>Average Days Present (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->student->name }}</td>
                        <td>{{ $attendance->student->section->grade }}-{{ $attendance->student->section->section }}</td>
                        <td>{{ $attendance->total_absent }}</td>
                        <td>{{ $attendance->total_present }}</td>
                        <td>{{ $attendance->student->SchoolYear->year }}</td>
                        <td>{{ number_format($attendance->average_days_present * 100, 2) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>
            <a href="{{ route('export.report', request()->except('page')) }}" class="btn btn-primary">Export To PDF</a>
        </div>
        <div class="d-flex justify-content-center">
            {{ $attendances->links('vendor.pagination.bootstrap-5') }}
        </div>
        @else
        <p class="text-center">No records filtered</p>
        @endif
    </div>
</div>
@endsection
