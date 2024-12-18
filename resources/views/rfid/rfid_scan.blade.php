@extends('layouts.master')

@section('page_title', 'RFID-Reader')

@section('content')
<!-- <style>
    .invisible-input {
        opacity: 0;
        position: absolute;
        z-index: -1;
    }
</style> -->

<div class="row justify-content-center">
    <div class='card col-8'>
        <div class='card-body' id="card_body">
            @include('partials.csrf_and_routes')
            <div id="alert_notif">
                @include('partials.alerts')
            </div>
            <h5 class='card-title'>Student Details</h5>
            <p class='card-text'>Name: <span id="name"></span></p>
            <p class='card-text'>Grade: <span id="grade"></span></p>
            <p class='card-text'>Section: <span id="section"></span></p>

            <form id='tag_form'>
                @csrf
                <label for='rfid_field' class='form-label'>RFID Tag</label>
                <input id='rfid_field' type='number' name='rfid_tag' class='form-control mb-2'>
                <button class='btn btn-primary' type='submit'>Verify</button>
            </form>
        </div>
        @if(!Auth::user()->isAdmin())
        <form id="mark_attendance">
            @csrf
            <ul id="list_students">

            </ul>
        </form>
        @endif
    </div>
</div>
<table>



</table>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        if(@json(Auth::user()->isTeacher())){
            iJustWannaGraduate();
                document.getElementById('mark_attendance').addEventListener('click', function(event) {
                    event.preventDefault(); 

                    fetch("{{ route('mark.attendance') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': routesAndToken.csrf,
                            'Accept': 'application/json',
                        },
                    })
                    .then((response) => response.json()) 
                    .then((data) => {
                        if (data.success) {
                            console.log('Attendance marked successfully');
                            sessionStorage.clear(); 

                            iJustWannaGraduate();
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                    });
                });
        }

        
        console.log("{{$sectionId ?? ''}}");
        console.log(sessionStorage.getItem('section'));
        if(sessionStorage.getItem('section') !== "{{$sectionId ?? ''}}"){
            sessionStorage.clear();
        }
        sessionStorage.setItem('section', "{{$sectionId ?? ''}}");


        if(sessionStorage.getItem('{{ Auth::user()->id }}')){
            generateStudents(JSON.parse(sessionStorage.getItem('{{ Auth::user()->id }}')));
        } 



        


        document.getElementById('rfid_field').focus();
        let alertNotif = document.getElementById('alert_notif');
        let routesAndToken = {
            verify: document.getElementById('app').getAttribute('data-verify-url'),
            csrf: document.getElementById('app').getAttribute('data-csrf-token')
        }; 


        document.getElementById('tag_form').addEventListener('submit', (event) => {
            event.preventDefault();
        

            let formData = new FormData(document.getElementById('tag_form'));
            formData.append('section', "{{ $sectionId ?? ''}}");
            fetch(routesAndToken.verify, {
                method: 'post',
                headers: {
                    'X-CSRF-TOKEN': routesAndToken.csrf,
                    'Accept': 'application/json',
                },
                body: formData,

            })
            .then((response) => {
                return response.json();
            })
            .then((data) => {
                if(data.success){
                    document.getElementById('rfid_field').value='';

                    document.getElementById('rfid_field').focus();
                    
                    console.log(data);
                    console.log(data.message);
                    showStudentVerified(data);
                    
                    if(data.from_teacher){

   
                        let sessionStudents = JSON.parse(sessionStorage.getItem('{{ Auth::user()->id }}')) || {};
                        if (!sessionStudents[data.student.id]) {
                            sessionStudents[data.student.id] = data.student;
                            sessionStorage.setItem('{{ Auth::user()->id }}', JSON.stringify(sessionStudents));
                        }
                        else{
                            sessionStudents[data.student.id] = data.student;
                            sessionStorage.setItem('{{ Auth::user()->id }}', JSON.stringify(sessionStudents));
                        }
                        generateStudents(sessionStudents);

                    }
                }
                else{
                    document.getElementById('rfid_field').value='';

                    document.getElementById('rfid_field').focus();
                     console.log(data.message);
                }

            })


        });

        function showStudentVerified(data) {

            document.getElementById('name').innerText = data.student.name;
            document.getElementById('grade').innerText = data.student.section.grade;
            document.getElementById('section').innerText = data.student.section.section;
            
        }
        function generateStudents(sessionStudents = {}){
            let studentList = ``;
            Object.values(sessionStudents).forEach((value) => {
                studentList += `<li> ${value.name} </li>`;
            });
            studentList ? studentList += `<button type="submit" class="btn btn-primary mb-2" id="mark_attendance">Mark attendance</button>` : ``;
            document.getElementById('list_students').innerHTML = studentList;

        }
        function iJustWannaGraduate(){
            let studentListElement = document.getElementById('list_students');
            if (studentListElement) {
                studentListElement.innerHTML = `<li class="text-muted">No students present so far...</li>`;
            }
        }
    });
</script>

@endsection