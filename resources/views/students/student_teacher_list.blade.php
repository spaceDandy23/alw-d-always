@extends('layouts.master')

@section('page_title', 'Watch List')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="p-4 text-center">
            @include('partials.alerts')
            <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                Add Class
            </button>
        </div>

        <div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form action="{{ route('create.class') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addSectionModalLabel">Select section for class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                        <div class="modal-body">
                            <div class="p-4">
                                <h5>Select Sections by Grade</h5>
                                <div class="row g-3">
                                    @for($grade = 7; $grade <= 12; $grade++)
                                        <div class="col-md-4">
                                            <h6>Grade {{ $grade }}</h6>
                                            @for($section = 1; $section <= 3; $section++)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="sections[]" id="grade{{ $grade }}_section{{ $section }}" value="{{ $grade }}-{{ $section }}">
                                                    <label class="form-check-label" for="grade{{ $grade }}_section{{ $section }}">
                                                        Section {{ $section }}
                                                    </label>
                                                </div>
                                            @endfor
                                        </div>
                                    @endfor
                                </div>
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

        @if($studentsGroupedBySection->isNotEmpty())
            @foreach($studentsGroupedBySection as $section => $students)
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 style="cursor: pointer;" onclick="toggleSection('{{ $section }}')">Section: {{ $section }}</h4>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSectionModal{{ $section }}">Delete</button>
                        <a href="{{ route('verify') }}" class="btn btn-primary btn-sm">Check Attendance</a>
                    </div>

                    <div class="card-body" id="section-{{ $section }}" style="display: none;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Grade</th>
                                    <th>Section</th>
                                    <th>Average times present</th>
                                    <th>Average times absent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <form action="{{ route('unenroll.student') }}" method="POST">
                                @csrf
                                <tbody>
                                    @foreach($students as $student)
                                        <tr>
                                            <td>{{ $student->name }}</td>
                                            <td>{{ $student->grade }}</td>
                                            <td>{{ $student->section }}</td>
                                            <td>{{ $student->average_present }} % </td>
                                            <td>{{ $student->average_absent }} % </td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#viewRecentActivityModal{{ $student->id }}">
                                                    View Recent Activities
                                                </button>
                                                <div class="d-flex align-items-center mb-2">
                                                    <label class="form-check-label me-2">
                                                        <input type="hidden" name="students[{{ $student->id }}]" value="1">
                                                        <input type="checkbox" name="students[{{ $student->id }}]" value="0" class="form-check-input"
                                                        {{ $student->pivot->enrolled === 1 ? '' : 'checked'}}>
                                                        {{ $student->pivot->enrolled === 1 ? 'Unenroll Student': 'Unenrolled'}}
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                        </table>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-danger btn-sm">Save changes</button>
                        </div>

                        </form>
                    </div>
                </div>

                <!-- Delete Section Modal -->
                <div class="modal fade" id="deleteSectionModal{{ $section }}" tabindex="-1" aria-labelledby="deleteSectionModalLabel{{ $section }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('class.delete') }}" method="POST">
                                @csrf
                                <input type="hidden" value="{{ $section }}" name="section">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteSectionModalLabel{{ $section }}">Confirm Deletion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete the section <strong>{{ $section }}</strong></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @foreach($students as $student)
                    <div class="modal fade" id="viewRecentActivityModal{{ $student->id }}" tabindex="-1" aria-labelledby="viewRecentActivityModalLabel{{ $student->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="viewRecentActivityModalLabel{{ $student->id }}">Recent Activity for {{ $student->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                
                                <div class="modal-body">
                                    <p>Recent activity details for {{ $student->name }}</p>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Morning Status</th>
                                                <th>Lunch Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ $student->recent_attendance->status_morning ?? 'No attendance recorded' }}</td>
                                                <td>{{ $student->recent_attendance->status_lunch ?? 'No attendance recorded' }}</td>
                                                <td>{{ $student->recent_attendance->date ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Time checked in</th>
                                                <th>Time checked out</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($student->recent_logs->isEmpty())
                                                <tr>
                                                    <td colspan="2" class="text-center">No recent logs yet</td>
                                                </tr>
                                            @else
                                                @foreach($student->recent_logs as $log)
                                                    <tr>
                                                        <td>{{ $log->check_in }}</td>
                                                        <td>{{ $log->check_out }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @else
            <p class="text-muted">No sections available.</p>
        @endif
    </div>
</div>

<script>
    function toggleSection(section) {
        const sectionElement = document.getElementById(`section-${section}`);
        if (sectionElement.style.display === "none" || sectionElement.style.display === "") {
            sectionElement.style.display = "block";
        } else {
            sectionElement.style.display = "none";
        }
    }
</script>

@endsection
