@extends('layouts.master')

@section('page_title', 'Class Report')

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
                                <option value="{{ $i }}">{{ $i === 1 ? ucfirst('present') : ucfirst('absent') }}</option>
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

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Name</th>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>Date</th>
                        <th>Present</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classAttendances as $student)
                    <tr>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->grade }}</td>
                        <td>{{ $student->section }}</td>
                        <td>{{ $student->pivot->date }}</td>
                        <td>{{ $student->pivot->present }}</td>
                        <td>{{ $student->pivot->time }}</td>
                        <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal-{{ $student->pivot->id }}">
                            Edit
                        </button>
                        </td>
                    </tr>
                    <div class="modal fade" id="editModal-{{ $student->pivot->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $student->pivot->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel-{{ $student->pivot->id }}">Edit Attendance</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('class.attendance.update', $student->pivot->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="present-{{ $student->pivot->id }}" class="form-label">Present</label>
                                            <select name="present" id="present-{{ $student->pivot->id }}" class="form-select">
                                                <option value="1" {{ $student->pivot->present == 1 ? 'selected' : '' }}>Present</option>
                                                <option value="0" {{ $student->pivot->present == 0 ? 'selected' : '' }}>Absent</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $classAttendances->links('vendor.pagination.bootstrap-5') }}
        </div>

    </div>
</div>
@endsection
