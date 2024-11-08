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
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('rfid_field').focus();
        let alertNotif = document.getElementById('alert_notif');
        let routesAndToken = {
            verify: document.getElementById('app').getAttribute('data-verify-url'),
            csrf: document.getElementById('app').getAttribute('data-csrf-token')
        }; 


        document.getElementById('tag_form').addEventListener('submit', (event) => {
            event.preventDefault();
            


            let formData = new FormData(document.getElementById('tag_form'));

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
                    console.log(data.student);
                    console.log(data.message);
                    showStudentVerified(data);
                }
                else{
                    console.log(data.message);
                }

            })


        });

        function showStudentVerified(data) {

            document.getElementById('name').innerText = data.student.name;
            document.getElementById('grade').innerText = data.student.grade;
            document.getElementById('section').innerText = data.student.section;
        }
    });
</script>

@endsection