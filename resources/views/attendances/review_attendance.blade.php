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

                                <form action="{{ route('edit.attendance') }}" method="GET" class="me-2">
                                    <input type="hidden" value="{{ $day->unique_dates }}" name="date">
                                    <button type="submit" class="btn btn-primary btn">Set Absent</button>
                                </form>
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
