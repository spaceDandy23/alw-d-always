@extends('layouts.master')

@section('page_title', 'Attendance')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="p-4">
            <form action="{{ route('attendances.reports.filter') }}" method="GET" class="mb-4">
                @include('partials.search_with_date')
            </form>
        </div>
        @if(isset($attendances))
        <table class="table table-striped">
            <thead>
                @include('partials.alerts')
                <tr>
                    <td colspan="7" class="text-center">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <h3>Total present and absent for {{ $totalNumbers['startDate'] ?? '' }} - {{ $totalNumbers['endDate'] ?? '' }}</h3>
                                <tr>
                                    <th scope="col">Total Absent</th>
                                    <th scope="col">Total Present</th>
                                    <th scope="col">Total Number Of Students</th>
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
                    </td>
                </tr>
                <tr>
                    <th scope="col" colspan="7">Attendance Report</th>
                </tr>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Grade</th>
                    <th scope="col">Section</th>
                    <th scope="col">Total Absents</th>
                    <th scope="col">Total Presents</th>
                    <th scope="col">School Year</th>
                    <th scope="col">Average Days Present (%)</th>
                </tr>
            </thead>
            <tbody>

                @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->student->name }}</td>
                        <td>{{ $attendance->student->grade }}</td>
                        <td>{{ $attendance->student->section }}</td>
                        <td>{{ $attendance->total_absent }}</td>
                        <td>{{ $attendance->total_present }}</td>
                        <td>{{ $attendance->student->SchoolYear->year }}</td>
                        <td>{{ number_format($attendance->average_days_present, 2) * 100 }} %</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $attendances->links('vendor.pagination.bootstrap-5') }}
        </div>
        @else
        <p>No records filtered</p>
        @endif
    </div>
</div>

@endsection
