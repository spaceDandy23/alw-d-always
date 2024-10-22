@extends('layouts.master')

@section('page_title', 'Attendance')

@section('content')

<div class="row justify-content-center">
    <div class="col-10">
        <table class="table table-striped">
            <thead>
                @include('partials.alerts')
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Date</th>
                    <th scope="col">Status (Morning)</th>
                    <th scope="col">Status (Lunch)</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->student->name  }}</td>
                        <td>{{ $attendance->date }}</td>
                        <td>{{ $attendance->status_morning }}</td>
                        <td>{{ $attendance->status_lunch }}</td>
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
