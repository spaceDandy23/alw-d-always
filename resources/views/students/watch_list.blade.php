@extends('layouts.master')

@section('page_title', 'Watch List')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="p-4 text-center">
            @include('partials.alerts')
            <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                Add Watch List
            </button>
        </div>

        <div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form action="{{ route('watchlist.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addSectionModalLabel">Add Section to Watch List</h5>
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
                            <button type="submit" class="btn btn-primary">Add Sections to Watch List</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if($studentsGroupedBySection->isNotEmpty())
            @foreach($studentsGroupedBySection as $section => $students)
                <div class="card mb-4">
                    <div class="card-header">
                        <!-- Make the section clickable -->
                        <h4 class="mb-0" style="cursor: pointer;" onclick="toggleSection('{{ $section }}')">Section: {{ $section }}</h4>
                    </div>

                    <div class="card-body" id="section-{{ $section }}" style="display: none;">
                        <!-- Display a table of students within the section -->
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Grade</th>
                                    <th>Section</th>
                                    <th>Average times present</th>
                                    <th>Average times absent</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    <tr>
                                        <td>{{ $student->name }}</td>
                                        <td>{{ $student->grade }}</td>
                                        <td>{{ $student->section }}</td>
                                        <td>{{ $student->average_present }} %</td>
                                        <td>{{ $student->average_absent }} %</td>
                                        <td>
                                            <a href="{{ route('students.show', $student->id) }}" class="btn btn-info btn-sm">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
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
