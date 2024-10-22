@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h2>Student Profile: {{ $student->name }}</h2>
                    @include('partials.alerts')
                </div>
                <div class="card-body">
                    <div class="student-info mb-3">
                        <p><strong>Student ID:</strong> {{ $student->id }}</p>
                        <p><strong>Grade:</strong> {{ $student->grade }}</p>
                        <p><strong>Section:</strong> {{ $student->section }}</p>
                        <p><strong>RFID Tag:</strong> {{ $student->tag->rfid_tag }}</p>
                        <p><strong>School Year:</strong> {{ $student->schoolYear->year }}</p>
                    </div>
                    <h3>Attendance Summary</h3>
                    <div class="attendance-summary mb-3">
                        <p><strong>Total Days Present (Morning):</strong> {{ $totalPresentMorning }}</p>
                        <p><strong>Total Days Present (Afternoon):</strong> {{ $totalPresentAfternoon }}</p>
                        <p><strong>Total Days Absent:</strong> {{ $totalAbsent }}</p>
                        <p><strong>Morning Attendance Percentage:</strong> {{ $attendancePercentageMorning }}%</p>
                        <p><strong>Afternoon Attendance Percentage:</strong> {{ $attendancePercentageAfternoon }}%</p>
                    </div>
                    <div class="p-4">
                        <form action="{{ route('student.filter', $student->id) }}" method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>   
                                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>
                    </div>

                    @if(isset($attendanceRecords))
                    <h3>Attendance Logs</h3>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Morning Status</th>
                                <th>Afternoon Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendanceRecords as $record)
                                <tr>
                                    <td>{{ $record->date }}</td>
                                    <td>{{ $record->status_morning }}</td>
                                    <td>{{ $record->status_lunch }}</td>
                                    <td>
                                        <a class="btn btn-warning" href="#" data-bs-toggle="modal" data-bs-target="#editAttendance{{ $record->id }}">Edit</a>
                                        <div class="modal fade" id="editAttendance{{ $record->id }}" tabindex="-1" aria-labelledby="editAttendanceLabel{{ $record->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editAttendanceLabel{{ $record->id }}">Edit Attendance</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('attendances.update', $record->id) }}" method="post">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" value="from_profile" name="from_profile">
                                                            <div class="mb-3">
                                                                <label for="status_morning_{{ $record->id }}" class="form-label">Status (Morning)</label>
                                                                <select class="form-select" id="status_morning_{{ $record->id }}" name="status_morning">
                                                                    <option value="present" {{ $record->status_morning == 'present' ? 'selected' : '' }}>Present</option>
                                                                    <option value="absent" {{ $record->status_morning == 'absent' ? 'selected' : '' }}>Absent</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="status_lunch_{{ $record->id }}" class="form-label">Status (Lunch)</label>
                                                                <select class="form-select" id="status_lunch_{{ $record->id }}" name="status_lunch">
                                                                    <option value="present" {{ $record->status_lunch == 'present' ? 'selected' : '' }}>Present</option>
                                                                    <option value="absent" {{ $record->status_lunch == 'absent' ? 'selected' : '' }}>Absent</option>
                                                                </select>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center">
                        {{ $attendanceRecords->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
                @endif
                <!-- <div class="card-footer text-center">
                    <a class="btn btn-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $student->id }}">Delete</a>
                </div> -->
            </div>
        </div>
    </div>
</div>
@endsection