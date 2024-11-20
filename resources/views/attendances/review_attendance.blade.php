@extends('layouts.master')

@section('page_title', 'Check')




@section('content')
@include('partials.alerts')
    @if($fullDay->isEmpty())
        <p>No days meet the criteria.</p>
    @else
    <h1>Attendance Overview</h1>
        <p>It seems that there are some days where the absenteeism is very high for lunch or morning attendance.</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Lunch Absenteeism (%)</th>
                    <th>Total Morning Absenteeism (%)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fullDay as $day)
                    <tr>
                        <td>{{ $day->unique_dates }}</td>
                        <td>{{ number_format($day->total_lunch_absent, 2) }}%</td>
                        <td>{{ number_format($day->total_morning_absent, 2) }}%</td>
                        <td> 
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#cancelAttendanceModal">
                                Cancel Attendance
                            </button>
                            <div class="modal fade" id="cancelAttendanceModal" tabindex="-1" aria-labelledby="cancelAttendanceModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="cancelAttendanceModalLabel">Cancel Attendance for Today</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('cancel.attendance.session') }}" method="POST">
                                            <div class="modal-body">
                                                <p>Choose which sessions to cancel attendance for:</p>

                                                    @csrf 
                                                    <input type="hidden"  name="from_review" value="{{ $day->unique_dates }}">
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


                                <form action="{{ route('edit.attendance') }}" method="GET">
                                    <input type="hidden" value="{{ $day->unique_dates }}" name="date">
                                    <button type="submit" class="btn btn-primary btn">Edit</button>
                                </form>
                            </td>
                        </div>
                    </tr>
                @endforeach
            </tbody>
        </table>


    @endif

@endsection
