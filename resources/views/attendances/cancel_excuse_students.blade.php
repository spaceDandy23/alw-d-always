@extends('layouts.master')

@section('page_title', 'Excuse Attendance')

@section('content')
<div class="row justify-content-center">
    <div class="col">
        <div class="p-4">
            @include('partials.alerts')
            <button type="button" class="btn btn-warning mb-3" data-bs-toggle="modal" data-bs-target="#cancelAttendanceModal">
                Cancel Attendance
            </button>
            <form action="{{ route('attendances.reports.filter') }}" method="GET" class="mb-4">
                <input type="hidden" value="from_cancel_excuse" name="from_cancel_excuse">
                @include('partials.search_with_date')
            </form>
            <form action="{{ route('excuse.cancel.apply') }}" method="POST">
                @csrf
                @if(isset($attendances))
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Name</th>
                            <th scope="col">Grade</th>
                            <th scope="col">Section</th>
                            <th scope="col">Status Morning</th>
                            <th scope="col">Status Lunch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->date }}</td>
                                <td>{{ $attendance->student->name }}</td>
                                <td>{{ $attendance->student->grade }}</td>
                                <td>{{ $attendance->student->section }}</td>
                                <td>
                                    <label>
                                        <input type="hidden" name="attendance[{{ $attendance->id }}][status_morning]" value="">
                                        <input type="checkbox" name="attendance[{{ $attendance->id }}][status_morning]" value="excused" 
                                        {{ $attendance->status_morning === 'excused' ? 'checked' : '' }}>
                                        Excused (Morning)
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="hidden" name="attendance[{{ $attendance->id }}][status_lunch]" value="">
                                        <input type="checkbox" name="attendance[{{ $attendance->id }}][status_lunch]" value="excused" 
                                        {{ $attendance->status_lunch === 'excused' ? 'checked' : '' }}>
                                        Excused (Lunch)
                                    </label>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center">
                    {{ $attendances->links('vendor.pagination.bootstrap-5') }}
                </div>
                <button type="submit" class="btn btn-primary mt-3">Apply Excuse</button>
                @else
                    <p>No attendance records found.</p>
                @endif
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="cancelAttendanceModal" tabindex="-1" aria-labelledby="cancelAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelAttendanceModalLabel">Cancel Attendance for Today</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('cancel.class.session') }}" method="POST">
                <div class="modal-body">
                    <p>Choose which sessions to cancel attendance for:</p>
                        @csrf 
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="cancel_morning" value="1" id="cancel_morning">
                            <label class="form-check-label" for="cancel_morning">
                                Cancel Morning Session
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="cancel_lunch" value="1" id="cancel_lunch">
                            <label class="form-check-label" for="cancel_lunch">
                                Cancel Lunch Session
                            </label>
                        </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
