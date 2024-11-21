@extends('layouts.master')

@section('page_title', 'Excuse Attendance')

@section('content')
<div class="row justify-content-center">
    <div class="col">
        <div class="p-4">
            <form action="{{ route('attendances.reports.filter') }}" method="GET" class="mb-4">
                <input type="hidden" value="from_excuse" name="from_excuse">
                @include('partials.search_with_date')
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
            </form>
            <form action="{{ route('excuse.apply') }}" method="POST">
                @csrf
                @if(isset($attendances))
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
                        @foreach($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->date }}</td>
                                <td>{{ $attendance->student->name }}</td>
                                <td>{{ $attendance->student->section->grade }}-{{ $attendance->student->section->section }}</td>
                                <td>
                                    @if($attendance->status_morning !== 'present')
                                    <label>
                                        <input type="hidden" name="attendance[{{ $attendance->id }}][status_morning]" value="">
                                        <input type="checkbox" name="attendance[{{ $attendance->id }}][status_morning]" value="present" 
                                        >
                                        Excused (Morning)
                                    </label>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->status_lunch !== 'present')
                                    <label>
                                        <input type="hidden" name="attendance[{{ $attendance->id }}][status_lunch]" value="">
                                        <input type="checkbox" name="attendance[{{ $attendance->id }}][status_lunch]" value="present" 
                                        >
                                        Excused (Lunch)
                                    </label>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary mt-3">Apply Excuse</button>
                @else
                    <p>No attendance records found.</p>
                @endif
            </form>
        </div>
    </div>
</div>
<script>

    console.log(@json($attendances));


</script>
@endsection
