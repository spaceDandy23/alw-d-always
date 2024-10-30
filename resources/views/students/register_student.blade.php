@extends('layouts.master')

@section('page_title', 'Register')

@section('content')

<div class="row justify-content-center"> 
    <div class="card col-10 px-0">
        <div class="card-header text-center">
            <h3>Register</h3>
        </div>
        <div class="card-body">
            @include('partials.alerts')
            @include('partials.csrf_and_routes')

            <div class="p-4 mt-4">
                <h5>Filter Students</h5>
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="filter_grade" class="form-label">Grade</label>
                    </div>
                    <div class="col">
                        <select class="form-select" id="filter_grade" name="grade">
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
                    <select class="form-select" id="filter_section" name="filter_section">
                        <option value="">-- Select Grade --</option>
                        @for($i = 1; $i <= 3; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    </div>
                    <div class="col-auto">
                        <label for="search_student" class="form-label">Student Name</label>
                    </div>
                    <div class="col">
                        <input type="text" name="search_student" id="search_student" class="form-control" placeholder="Enter Student Name">
                    </div>
                    <div class="col-auto">
                        <button id="filter_button" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </div>



            <form action="{{ route('register.student.parent') }}" id="form_register" method="POST">
                @csrf
                <input type="hidden" name="student_id" id="student_id" >
                <label for="rfid_tag" class="form-label">RFID Tag</label>
                <input type="number" name="rfid_tag" id="rfid_tag" class="form-control">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control" readonly>
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control" readonly>
                <label for="grade" class="form-label">Grade</label>
                <input type="text" name="grade" id="grade" class="form-control" readonly>
                <label for="section" class="form-label">Section</label>
                <input type="text" name="section" id="section" class="form-control mb-2" readonly>
                <label for="guardian_first_name" class="form-label">Guardian's First Name</label>
                <input type="text" name="guardian_first_name" id="guardian_first_name" class="form-control mb-2">
                <label for="guardian_last_name" class="form-label">Guardian's Last Name</label>
                <input type="text" name="guardian_last_name" id="guardian_last_name" class="form-control mb-2">
                <label for="phone_number" class="form-label">Guardian Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" class="form-control mb-2">
                <label for="relationship" class="form-label">Relationship</label>
                <select id="relationship" class="form-select mb-2" name="relationship">
                        <option value="">-- Select Relationship --</option>
                        @foreach ($relationships as $relationship )
                            <option value="{{$relationship}}">{{$relationship}}</option>
                        @endforeach
                    </select>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>

            <table class="table table-striped mt-3">
                <thead>
                    <th scope="col">Name</th>
                    <th scope="col">Grade</th>
                    <th scope="col">Section</th>
                </thead>
                <tbody id="students_searched">
                    <!-- Student rows will be populated here -->
                </tbody>
            </table>

            <div class="row justify-content-center">
                <div class="col-12 mb-3 text-center">
                    <p class="small text-muted" id="pagination_caption"></p>
                </div>
                
                <div class="col-12">
                    <ul id="pagination" class="pagination justify-content-center mb-0"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module" src="{{ asset('js/registerStudent.js') }}"></script>

@endsection
