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
                                <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Check Attendance</button>
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
                                <ul class="list-group">
                                    @foreach($students as $student)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $student->name }} (ID: {{ $student->id }})
                                            
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
                                        </li>
                                    @endforeach
                                </ul>
                                <button type="submit" class="btn btn-warning mt-3">Save changes</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach

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
        if (sectionElement.style.display === "none" || sectionElement.style.display === "") {
            sectionElement.style.display = "block";
        } else {
            sectionElement.style.display = "none";
        }
    }

    function setRemoveClassId(sectionId) {
        document.getElementById('sectionIdToRemove').value = sectionId;
    }
</script>

@endsection
