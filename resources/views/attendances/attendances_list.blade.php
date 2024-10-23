@extends('layouts.master')

@section('page_title', 'Attendance')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="p-4">
            <form action="{{ route('attendances.filter') }}" method="GET" class="mb-4">
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
        <table class="table table-striped">
            <thead>
                @include('partials.alerts')
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Grade</th>
                    <th scope="col">Section</th>
                    <th scope="col">Date</th>
                    <th scope="col">Status (Morning)</th>
                    <th scope="col">Status (Lunch)</th>
                    <th scope="col">School Year</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->student->name  }}</td>
                        <td>{{ $attendance->student->grade }}</td>
                        <td>{{ $attendance->student->section }}</td>
                        <td>{{ $attendance->date }}</td>
                        <td>{{ $attendance->status_morning }}</td>
                        <td>{{ $attendance->status_lunch }}</td>
                        <td>{{ $attendance->student->schoolYear->year }}</td>
                        <td>
                            <a class="btn btn-warning" href="#" data-bs-toggle="modal" data-bs-target="#editAttendance{{ $attendance->id }}">Edit</a>
                            <!-- Edit Attendance Modal -->
                            <div class="modal fade" id="editAttendance{{ $attendance->id }}" tabindex="-1" aria-labelledby="editAttendanceLabel{{ $attendance->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editAttendanceLabel{{ $attendance->id }}">Edit Attendance</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('attendances.update', $attendance->id) }}" method="post">
                                                @csrf
                                                @method('PUT')
                                                <label for="status_morning_{{ $attendance->id }}" class="form-label">Status (Morning)</label>
                                                <select class="form-select" id="status_morning_{{ $attendance->id }}" name="status_morning">
                                                    <option value="present" {{ $attendance->status_morning == 'present' ? 'selected' : '' }}>Present</option>
                                                    <option value="absent" {{ $attendance->status_morning == 'absent' ? 'selected' : '' }}>Absent</option>
                                                </select>
                                                <label for="status_lunch_{{ $attendance->id }}" class="form-label">Status (Lunch)</label>
                                                <select class="form-select" id="status_lunch_{{ $attendance->id }}" name="status_lunch">
                                                    <option value="present" {{ $attendance->status_lunch == 'present' ? 'selected' : '' }}>Present</option>
                                                    <option value="absent" {{ $attendance->status_lunch == 'absent' ? 'selected' : '' }}>Absent</option>
                                                </select>
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
            {{ $attendances->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
