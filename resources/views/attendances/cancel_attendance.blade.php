@extends('layouts.master')

@section('page_layout', 'Cancel Attendance')

@section('content')

<div class="d-flex justify-content-center">
    <div class="card shadow col-6">
        <div class="card-header">
            <h4 class="mb-0">Cancel</h4>
        </div>
        <div class="card-body">
            @include('partials.alerts')

            <form action="" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="date" class="form-label">Select Date</label>
                    <input 
                        type="date" 
                        id="date" 
                        name="date" 
                        class="form-control" 
                        max="{{ now()->toDateString() }}" 
                        required>
                </div>
                
                <div class="mb-4">
                    <label for="session" class="form-label">Select Session</label>
                    <select id="session" name="session" class="form-select">
                        <option value="">-- Select Session --</option>
                        <option value="morning">Morning</option>
                        <option value="lunch">Lunch</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Cancel Attendance</button>
            </form>
        </div>
    </div>
</div>

@endsection
