@extends('layouts.master')

@section('page_title', 'Students')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="d-flex justify-content-center mb-2">
            <a href="#" class="btn btn-secondary mx-4" data-bs-toggle="modal" data-bs-target="#uploadCSV">Upload CSV</a>
            <button class="btn btn-danger mx-4" data-bs-toggle="modal" data-bs-target="#undoImportModal">Undo Import</button>
        </div>

        <div class="p-4">
            <form action="{{ route('students.filter') }}" method="GET" class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="name" class="form-label">Student Name</label>
                    </div>
                    <div class="col">
                        <input type="text" name="name" id="search_student" class="form-control" placeholder="Enter Student Name">
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
                    <th scope="col">RFID Tag</th>
                    <th scope="col">Name</th>
                    <th scope="col">Section</th>
                    <th scope="col">School Year</th>
                    <th scope="col">Associated Guardian</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    <tr>
                        <td>{{ $student->tag->rfid_tag ?? 'None' }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->section->grade }}-{{ $student->section->section }}</td>
                        <td>{{ $student->schoolYear->year ?? 'No School Year' }}</td>
                        <td> @foreach ($student->guardians as $guardian )
                            <li>
                                {{ $guardian->name }} - {{ ucfirst($guardian->pivot->relationship_to_student) }}
                            </li>
                        @endforeach
                        </td>
                        <td>
                        <a class="btn btn-warning" href="#" data-bs-toggle="modal" data-bs-target="#editStudent{{ $student->id }}">Edit</a>

                        <a class="btn btn-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $student->id }}">Delete</a>

                        <a class="btn btn-primary" href="{{ route('student.profile', $student->id) }}">
                            View Profile
                        </a>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#viewTagHistory{{ $student->id }}">
                            View Tag History
                        </button>
                        <div class="modal fade" id="viewTagHistory{{ $student->id }}" tabindex="-1" aria-labelledby="viewTagHistoryLabel{{ $student->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewTagHistoryLabel{{ $student->id }}">Tag History for {{ $student->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if($student->tagHistories->isEmpty())
                                            <p>No tag history available for this student.</p>
                                        @else
                                            <ul class="list-group">
                                                @foreach($student->tagHistories as $tag)
                                                    <li class="list-group-item">
                                                        <strong>RFID Tag:</strong> {{ $tag->tag->rfid_tag }} <br>
                                                        <strong>Updated On:</strong> {{ $tag->created_at->format('F j, Y h:i A') }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="editStudent{{ $student->id }}" tabindex="-1" aria-labelledby="editStudentLabel{{ $student->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editStudentLabel{{ $student->id }}">Edit Student</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    
                                    <div class="modal-body">
                                        <form action="{{ route('students.update', $student->id) }}" method="post" id="form_edit">
                                            @csrf
                                            @method('PUT')
                                            @if (empty($student->tag))
                                                <div class="alert alert-warning" role="alert">
                                                    This student does not have a registered RFID tag. Please register an RFID tag before editing.
                                                </div>
                                            @endif
                                            <label for="rfid_tag{{ $student->id }}">Change RFID Tag</label>
                                            <input type="text" class="form-control" id="rfid_tag_{{ $student->id }}" name="rfid_tag" value="{{ $student->tag->rfid_tag ?? '' }}"  
                                            
                                            {{ $student->tag ? '' : 'readonly' }}>


                                            <label for="name_{{ $student->id }}" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name_{{ $student->id }}" name="name" value="{{ $student->name }}">


                                            <label for="section_{{ $student->id }}" class="form-label">Section</label>
                                            <select class="form-select" id="section_{{ $student->id }}" name="section_id">
                                                @foreach($sections as $section)
                                                    <option value="{{ $section->id }}" {{ $student->section_id === $section->id ? 'selected' : '' }}>{{ $section->grade }}-{{ $section->section }}</option>
                                                @endforeach
                                            </select>


                                            <label for="school_year_{{ $student->id }}" class="form-label">School Year</label>
                                            <input type="text" name="school_year" class="form-control" id="school_year" value="{{ $student->schoolYear->year ?? 'No School Year' }}"readonly>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="deleteModal{{ $student->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $student->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel{{ $student->id }}">Confirm Deletion</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete "{{ $student->name }}"?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form action="{{ route('students.destroy', $student->id) }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
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
            {{ $students->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
<!-- Upload CSV Modal -->
<div class="modal fade" id="uploadCSV" tabindex="-1" aria-labelledby="uploadCSVLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadCSVLabel">Upload CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('importCSV') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">CSV File</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv">


                        <label for="start_year" class="form-label">Start Year</label>
                        <input type="number" class="form-control" id="start_year" name="start_year">

                        <label for="end_year" class="form-label">End Year</label>
                        <input type="text" class="form-control" id="end_year" name="end_year" readonly>
                    </div>
                    @if($activeSchoolYear)
                    <div class="alert alert-warning" id="uploadWarning">
                        <strong>Warning!</strong> Uploading the CSV with the same school year will add new students to the existing batch. Please confirm before proceeding.
                    </div>
                    @endif
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Upload CSV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="undoImportModal" tabindex="-1" aria-labelledby="undoImportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="undoImportModalLabel">Undo Batch Import</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to undo one of the following import batches? This action will remove all records that were added in the selected import batch.</p>
                @if($importBatches->count() > 0)
                    <ul class="list-group">
                        @foreach ($importBatches as $batch)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Batch ID: {{ $batch->id }} - Imported On: {{ $batch->created_at->format('F j, Y h:i A') }},
                                Batch Name: {{ $batch->batch_name }}
                                <form action="{{ route('import.undo', $batch->id) }}" method="POST" class="ms-2">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">Undo</button>
                                </form>
                            </li>
  
                        @endforeach
                    </ul>
                @else
                    <p>No import batches available.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#form_edit').forEach(element => {
        element.addEventListener('keydown', function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
            }
        });
    });

    document.getElementById('start_year').addEventListener('input', function(event){
        document.getElementById('end_year').value = parseInt(event.target.value) + 1;
    });
});
</script>
@endsection
