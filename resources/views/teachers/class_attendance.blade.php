@extends('layouts.master')

@section('page_title', 'Class Attendance Report')

@section('content')
<div class="row justify-content-center">
    <div class="col">
        <div class="p-4">
            <!-- Search Form -->
            <form action="{{ route('class.filter') }}" method="GET" class="mb-4">
                @include('partials.search_with_date')
                <div class="row g-3 mt-2 align-items-center">
                    <div class="col-auto">
                        <label for="status" class="form-label">Status</label>
                    </div>
                    <div class="col">
                        <select class="form-select" name="status" id="status">
                            <option value="">-- Select Status --</option>
                            @for($i = 0; $i <= 1; $i++)
                                <option value="{{ $i }}" >
                                    {{ $i === 1 ? ucfirst('present') : ucfirst('absent') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-auto">
                        <label for="start_time" class="form-label">Start Time</label>
                    </div>
                    <div class="col">
                        <input id="start_time" type="time" name="start_time" class="form-control">
                    </div>
                    <div class="col-auto">
                        <label for="end_time" class="form-label">End Time</label>
                    </div>
                    <div class="col">
                        <input id="end_time" type="time" name="end_time" class="form-control">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Attendance Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendanceSection as $attendance)
                        <tr>
                            <td>{{ $attendance->student->name }}</td>
                            <td>{{ $attendance->section->grade }}</td>
                            <td>{{ $attendance->section->section }}</td>
                            <td>{{ $attendance->date }}</td>
                            <td>{{ $attendance->time }}</td>
                            <td>{{ $attendance->present ? 'Present' : 'Absent' }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal-{{ $attendance->id }}">
                                    Edit
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="editModal-{{ $attendance->id }}" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('class.attendance.update', $attendance->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel">Edit Attendance</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="status-{{ $attendance->id }}" class="form-label">Status</label>
                                                <select name="present" id="status_{{ $attendance->id }}" class="form-select">
                                                    <option value="1" {{ $attendance->present ? 'selected' : '' }}>Present</option>
                                                    <option value="0" {{ !$attendance->present ? 'selected' : '' }}>Absent</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $attendanceSection->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
