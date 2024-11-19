@extends('layouts.master')

@section('page_title', 'Edit Attendance')

@section('content')
<div class="row justify-content-center">
    <h1>Select students who were absent {{ request('date') }}</h1>
    <div class="col">
        <div class="p-4">
            <form action="{{ route('edit.attendance') }}" method="GET" class="mb-4">
            <input type="hidden" name="date" value="{{ request('date') }}">
            <div class="row g-3 align-items-center">
                @include('partials.alerts')
                <div class="col-auto">
                    <label for="name" class="form-label">Student Name</label>
                </div>
                <div class="col">
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter Student Name">
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
            </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
            </form>
            @if($queryAttendance->count())
                <form id="save_changes">
                    @csrf
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Name</th>
                                <th scope="col">Section</th>
                                <th scope="col">Status Morning</th>
                                <th scope="col">Status Lunch</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($queryAttendance as $attendance)
                        <tr>
                            <td>{{ $attendance->date }}</td>
                            <td>{{ $attendance->student->name }}</td>
                            <td>{{ $attendance->student->section->grade }}-{{ $attendance->student->section->section }}</td>
                            
                            <td>
                                <label class="form-check-label">
                                    <input type="checkbox" name="status_morning_{{ $attendance->id }}" value="absent" class="form-check-input">
                                    Absent (Morning)
                                </label>
                            </td>
                            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                            <td>
                                <label class="form-check-label">
                                    <input type="checkbox" name="status_lunch_{{ $attendance->id }}" value="absent" class="form-check-input">
                                    Absent (Lunch)
                                </label>
                            </td>
                        </tr>
                    @endforeach
                        </tbody>
                    </table>
                </form>

                <form action="{{ route('edit.attendance') }}" method="POST" id="save_changes">
                @csrf
                <input type="hidden" name="date" value="{{ request('date') }}">

                <table class="table table-striped" id="added_students">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Status Morning</th>
                            <th scope="col">Status Lunch</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                <div class="mt-3" id="submit-button-container" style="display: none;">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
                <div class="mt-3">
                    <button type="button" id="clear-session-storage" class="btn btn-warning">Clear Selection</button>
                </div>
            </form>
            @else
                <p>No attendance records found for the selected date.</p>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {


    if(sessionStorage.getItem('date') !== "{{ request('date') }}"){

        sessionStorage.clear();


    }

    sessionStorage.setItem('date', "{{ request('date') }}");

    const addedStudents = JSON.parse(sessionStorage.getItem('addedStudents')) || [];
    const addedStudentsTable = document.getElementById('added_students').getElementsByTagName('tbody')[0];
    const submitButtonContainer = document.getElementById('submit-button-container');
    const form = document.getElementById('save_changes');
    const clearButton = document.getElementById('clear-session-storage');

    function renderTable() {
        addedStudentsTable.innerHTML = '';
        addedStudents.forEach((student, index) => {
            const row = addedStudentsTable.insertRow();
            row.innerHTML = `
                <td>
                    ${student.name}
                    <input type="hidden" name="students[${index}][id]" value="${student.id}">
                    <input type="hidden" name="students[${index}][name]" value="${student.name}">
                </td>
                <td>
                    ${student.status_morning}
                    <input type="hidden" name="students[${index}][status_morning]" value="${student.status_morning}">
                </td>
                <td>
                    ${student.status_lunch}
                    <input type="hidden" name="students[${index}][status_lunch]" value="${student.status_lunch}">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-student" data-index="${index}">Remove</button>
                </td>
            `;
        });

        submitButtonContainer.style.display = addedStudents.length > 0 ? 'block' : 'none';
    }

    renderTable();

    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const studentRow = this.closest('tr');
            const studentId = studentRow.querySelector('input[name^="attendance_id"]').value;
            const studentName = studentRow.querySelector('td:nth-child(2)').textContent.trim();

            let statusMorning = studentRow.querySelector('input[name="status_morning_' + studentId + '"]').checked ? 'absent' : 'present';
            let statusLunch = studentRow.querySelector('input[name="status_lunch_' + studentId + '"]').checked ? 'absent' : 'present';

            const existingStudentIndex = addedStudents.findIndex(student => student.id === studentId);

            if (existingStudentIndex !== -1) {
                addedStudents[existingStudentIndex].status_morning = statusMorning;
                addedStudents[existingStudentIndex].status_lunch = statusLunch;
            } else {
                addedStudents.push({
                    id: studentId,
                    name: studentName,
                    status_morning: statusMorning,
                    status_lunch: statusLunch
                });
            }

            sessionStorage.setItem('addedStudents', JSON.stringify(addedStudents));
            renderTable();
        });
    });

    addedStudentsTable.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-student')) {
            const index = e.target.dataset.index;
            addedStudents.splice(index, 1);
            sessionStorage.setItem('addedStudents', JSON.stringify(addedStudents));
            renderTable();
        }
    });

    clearButton.addEventListener('click', function () {
        sessionStorage.removeItem('addedStudents');
        addedStudents.length = 0; 
        renderTable();
    });


});

</script>
@endsection
