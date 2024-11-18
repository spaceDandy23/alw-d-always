@extends('layouts.master')

@section('page_title', 'Class List')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="p-4">
            <h3 class="text-center mb-4">Class List</h3>
            <div class="text-center mb-4">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                    <i class="bi bi-plus-circle"></i> Add Class
                </button>
            </div>
            @include('partials.alerts')
            @foreach($groupedBySection as $section => $students)
                <div class="card mb-3 shadow-sm hover-shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="h5 mb-0">Section: {{ $section }}</span>
                        <div>
                            <button 
                                class="btn btn-primary mx-2" 
                                onclick="toggleSection('{{ $section }}')">
                                <i class="bi bi-chevron-down"></i> Show Students
                            </button>
                            <form action="{{ route('verify') }}" class="d-inline-block">
                                <input type="hidden" name="section" value="{{ $section }}">
                                <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Check Class Attendance</button>
                            </form>
                            <button 
                                class="btn btn-danger mx-2" 
                                data-bs-toggle="modal" 
                                data-bs-target="#removeClassModal" 
                                onclick="setRemoveClassId('{{ $section }}')">
                                 <i class="bi bi-trash"></i> Remove Class
                            </button>
                        </div>
                    </div>

                    <div class="card-body" id="section-{{ $section }}" style="display: none;">
                        @if($students->isEmpty())
                            <p class="text-muted">No students in this section.</p>
                        @else
                            <form action="{{ route('unenroll.students') }}" method="POST">
                                @csrf
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Attendance</th>
                                            <th>RFID Logs</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            <tr>
                                                <td>{{ $student->name }} (ID: {{ $student->id }})</td>
                                                <td>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-info btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#attendanceModal{{ $student->id }}">
                                                        View Attendance
                                                    </button>
                                                    <!-- Attendance Modal -->
                                                    <div class="modal fade" id="attendanceModal{{ $student->id }}" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="attendanceModalLabel">Attendance for {{ $student->name }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    @foreach($student->attendances as $attendance)
                                                                        <div class="mb-3">
                                                                            <p>Date: {{ $attendance->date }}</p>
                                                                            <p>Status Morning: {{ $attendance->status_morning ?? 'N/A' }}</p>
                                                                            <p>Status Lunch: {{ $attendance->status_lunch ?? 'N/A' }}</p>
                                                                        </div>
                                                                    @endforeach
                                                                    @if($student->attendances->isEmpty())
                                                                        <p class="text-muted">No records so far.</p>
                                                                    @endif
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-warning btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#rfidModal{{ $student->id }}">
                                                        View RFID Logs
                                                    </button>
                                                    <!-- RFID Modal -->
                                                    <div class="modal fade" id="rfidModal{{ $student->id }}" tabindex="-1" aria-labelledby="rfidModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="rfidModalLabel">RFID Logs for {{ $student->name }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    @foreach($student->rfidLogs as $log)
                                                                        <div class="mb-3">
                                                                            <p>Date: {{ $log->date }}</p>
                                                                            <p>Check-in: {{ $log->check_in ?? 'N/A' }}</p>
                                                                            <p>Check-out: {{ $log->check_out ?? 'N/A' }}</p>
                                                                        </div>
                                                                    @endforeach
                                                                    @if($student->rfidLogs->isEmpty())
                                                                        <p class="text-muted">No records so far.</p>
                                                                    @endif
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <input type="hidden" name="students[{{$student->id}}][]" value=" ">
                                                        <input 
                                                            type="checkbox" 
                                                            name="students[{{$student->id}}][]" 
                                                            value=" " 
                                                            class="form-check-input" 
                                                            {{ !$student->pivot->enrolled ? 'checked' : '' }}>
                                                        <label for="unenroll_{{ $student->name }}" class="form-check-label">{{ !$student->pivot->enrolled ? 'Unenrolled' : 'Unenroll'}}</label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <button type="submit" class="btn btn-warning mt-3">Save changes</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Modal for Adding Sections -->
            <div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="{{ route('create.class') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="addSectionModalLabel">Select Sections</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    @foreach($sections as $section)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input" 
                                                    type="checkbox" 
                                                    name="sections[]" 
                                                    id="section{{ $section->id }}" 
                                                    value="{{ $section->id }}">
                                                <label class="form-check-label" for="section{{ $section->id }}">
                                                    Section {{ $section->grade }}-{{ $section->section }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal for Removing Class -->
            <div class="modal fade" id="removeClassModal" tabindex="-1" aria-labelledby="removeClassModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="removeClassModalLabel">Remove Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to remove this class? This action cannot be undone.
                        </div>
                        <div class="modal-footer">
                            <form action="{{ route('class.delete') }}" method="POST">
                                @csrf
                                <input type="hidden" name="section_id" id="sectionIdToRemove">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Remove Class</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function toggleSection(section) {
        const sectionElement = document.getElementById(`section-${section}`);
        sectionElement.style.display = sectionElement.style.display === "none" || sectionElement.style.display === "" ? "block" : "none";
    }

    function setRemoveClassId(section) {
        document.getElementById('sectionIdToRemove').value = section;
    }
</script>

@endsection
