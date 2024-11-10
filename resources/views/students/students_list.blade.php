@extends('layouts.master')

@section('page_title', 'Students')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="d-flex justify-content-center mb-2">
            <!-- <a href="#" class="btn btn-primary mx-4" data-bs-toggle="modal" data-bs-target="#createStudent">Add Student</a> -->
            <a href="#" class="btn btn-secondary mx-4" data-bs-toggle="modal" data-bs-target="#uploadCSV">Upload CSV</a>
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
                    <th scope="col">Grade</th>
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
                        <td>{{ $student->grade }}</td>
                        <td>{{ $student->section }}</td>
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
                        <!-- Edit Student Modal -->
                        <div class="modal fade" id="editStudent{{ $student->id }}" tabindex="-1" aria-labelledby="editStudentLabel{{ $student->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editStudentLabel{{ $student->id }}">Edit Student</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('students.update', $student->id) }}" method="post">
                                            @csrf
                                            @method('PUT')

                                            <label for="rfid_tag{{ $student->id }}">Rfid Tag</label>
                                            <input type="text" class="form-control" id="rfid_tag_{{ $student->id }}" name="rfid_tag" value="{{ $student->tag->rfid_tag ?? '' }}">


                                            <label for="name_{{ $student->id }}" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name_{{ $student->id }}" name="name" value="{{ $student->name }}">

                                            <label for="grade_{{ $student->id }}" class="form-label">Grade</label>
                                            <select class="form-select" id="grade_{{ $student->id }}" name="grade">
                                                @for($i = 7; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ $student->grade == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>

                                            <label for="section_{{ $student->id }}" class="form-label">Section</label>
                                            <select class="form-select" id="section_{{ $student->id }}" name="section">
                                                @for($i = 1; $i <= 3; $i++)
                                                    <option value="{{ $i }}" {{ $student->section == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
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

                            <!-- Delete Modal -->
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
<!-- Create Student Modal -->
<div class="modal fade" id="createStudent" tabindex="-1" aria-labelledby="createStudentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createStudentLabel">Add New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('students.store') }}" method="post">
                    @csrf
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name">

                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name">


                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name">


                    <label for="grade" class="form-label">Grade</label>
                    <select class="form-select" id="grade" name="grade">
                        <option value="">-- Select Grade --</option>
                        @for($i = 7; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <label for="section" class="form-label">Section</label>
                    <select class="form-select" id="section" name="section">
                        <option value="">-- Select Section --</option>
                        @for($i = 1; $i <= 3; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>


                    <label for="guardian_last_name" class="form-label">Guardian Last Name</label>
                    <input type="text" class="form-control" id="guardian_last_name" name="guardian_last_name">

                    <label for="guardian_first_name" class="form-label">Guardian First Name</label>
                    <input type="text" class="form-control" id="guardian_first_name" name="guardian_first_name">

                    <label for="relationship" class="form-label">Relationship</label>
                    <select id="relationship" class="form-select" name="relationship">
                        <option value="">-- Select Relationship --</option>
                        @foreach ($relationships as $relationship )
                            <option value="{{$relationship}}">{{$relationship}}</option>
                        @endforeach
                    </select>
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number">



                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Student</button>
                    </div>
                </form>
            </div>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Upload CSV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {

    document.getElementById('start_year').addEventListener('input', function(event){

        document.getElementById('end_year').value = parseInt(event.target.value) + 1;


    });

});
</script>
@endsection
