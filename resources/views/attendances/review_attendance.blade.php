@extends('layouts.master')

@section('page_title', 'Check')

@section('content')
@include('partials.alerts')
    @if($fullDay)
    <h1>Attendance Overview</h1>
        <p>It seems that there are some days where the absenteeism is very high for lunch or morning attendance in the past 5 days</p>
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

                                <form action="{{ route('edit.attendance') }}" method="GET" class="mb-2">
                                    <input type="hidden" value="{{ $day->unique_dates }}" name="date">
                                    <button type="submit" class="btn btn-primary">Set Absent</button>
                                </form>

                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal">Was it a holiday</button>
                                <div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="holidayModalLabel">Set to holiday</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-warning" role="alert" >
                                                    Setting day to holiday will remove any attendance records from this day 
                                                </div>
                                                <form action="{{ route('set.day') }}" method="POST" id="removeDay">
                                                @csrf
                                                <input type="hidden" value="{{ $day->unique_dates }}" name="date">

                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" form="removeDay" class="btn btn-primary">Set</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <form action="{{ route('cancel.attendance.session') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="from_review" value="{{ $day->unique_dates }}">

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="cancel_morning" value="1" id="cancel_morning_{{ $day->unique_dates }}">
                                        <label class="form-check-label" for="cancel_morning_{{ $day->unique_dates }}">
                                            Cancel Morning Session
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="cancel_lunch" value="1" id="cancel_lunch_{{ $day->unique_dates }}">
                                        <label class="form-check-label" for="cancel_lunch_{{ $day->unique_dates }}">
                                            Cancel Lunch Session
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-warning btn">Cancel Attendance</button>
                                </form>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
