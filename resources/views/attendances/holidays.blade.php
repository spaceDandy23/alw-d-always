@extends('layouts.master')

@section('page_title', 'Special Occasions')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <div class="d-flex justify-content-center mb-2">
            <a href="#" class="btn btn-primary mx-4" data-bs-toggle="modal" data-bs-target="#createOccasion">Add Special Occasion</a>
        </div>
        <table class="table table-striped">
            <thead>
                @include('partials.alerts')
                <tr>
                    <th scope="col">Occasion Name</th>
                    <th scope="col">Date</th>
                    <th scope="col">Description</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($holidays as $holiday)
                    <tr>
                        <td>{{ $holiday->name }}</td>
                        <td>{{ $holiday->month }} - {{ $holiday->day }}</td>
                        <td>{{ $holiday->description }}</td>
                        <td>                            
                            <a class="btn btn-warning" href="#" data-bs-toggle="modal" data-bs-target="#editOccasion{{ $holiday->id }}">Edit</a>
                            <a class="btn btn-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteOccasion{{ $holiday->id }}">Delete</a>

                            <!-- Edit Occasion Modal -->
                            <div class="modal fade" id="editOccasion{{ $holiday->id }}" tabindex="-1" aria-labelledby="editOccasionLabel{{ $holiday->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editOccasionLabel{{ $holiday->id }}">Edit Occasion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('holidays.update', $holiday->id) }}" method="post">
                                                @csrf
                                                @method('PUT')
                                                <label for="name_{{ $holiday->id }}" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="name_{{ $holiday->id }}" name="name" value="{{ $holiday->name }}">
                                                
                                                <div class="row g-3 align-items-center">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="month_{{ $holiday->id }}" class="form-label">Month</label>
                                                        <select class="form-select" id="month_{{ $holiday->id }}" name="month">
                                                            <option value="">-- Select Month --</option>
                                                            @for ($month = 1; $month <= 12; $month++)
                                                                <option value="{{ $month }}" {{ $holiday->month == $month ? 'selected' : '' }}>
                                                                    {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                                                                </option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="day_{{ $holiday->id }}" class="form-label"> Day</label>
                                                        <select class="form-select" id="day_{{ $holiday->id }}" name="day">
                                                            <option value="">-- Select Day --</option>
                                                            @for ($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $holiday->month, date('Y')); $day++)
                                                                <option value="{{ $day }}" {{ $holiday->day == $day ? 'selected' : '' }}>{{ $day }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <label for="description_{{ $holiday->id }}" class="form-label">Description</label>
                                                <textarea class="form-control" id="description_{{ $holiday->id }}" name="description">{{ $holiday->description }}</textarea>
                                                
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Occasion Modal -->
                            <div class="modal fade" id="deleteOccasion{{ $holiday->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteOccasionLabel{{ $holiday->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteOccasionLabel{{ $holiday->id }}">Confirm Deletion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete "{{ $holiday->name }}"?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="{{ route('holidays.destroy', $holiday->id) }}" method="post">
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

        <!-- Pagination Links (optional) -->
        <div class="d-flex justify-content-center">
            {{ $holidays->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Create Special Occasion Modal -->
<div class="modal fade" id="createOccasion" tabindex="-1" aria-labelledby="createOccasionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createOccasionLabel">Add New Occasion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('holidays.store') }}" method="post">
                    @csrf
                    <label for="name" class="form-label">Occasion Name</label>
                    <input type="text" class="form-control" id="name" name="name">
                    
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6 mb-3">
                            <label for="month" class="form-label">Month</label>
                            <select class="form-select" id="month" name="month">
                                <option value="">-- Select Month --</option>
                                @for ($month = 1; $month <= 12; $month++)
                                    <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="day" class="form-label">Start Day</label>
                            <select class="form-select" id="day" name="day">
                                <option value="">-- Select Day --</option>
                            </select>
                        </div>
                    </div>
                    
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Occasion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('js/setDaysForMonth.js') }}" type="module"></script>
@endsection
