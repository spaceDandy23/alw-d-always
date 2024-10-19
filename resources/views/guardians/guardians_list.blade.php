@extends('layouts.master')

@section('page_title', 'Guardians')

@section('content')

<div class="row justify-content-center">
    <div class="col-10">
        <div class="d-flex justify-content-center mb-2">
            <a href="#" class="btn btn-primary mx-4" data-bs-toggle="modal" data-bs-target="#createGuardian">Add Guardian</a>
        </div>
        <table class="table table-striped">
            <thead>
                @include('partials.alerts')
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Relationship</th>
                    <th scope="col">Phone Number</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guardians as $guardian)
                    <tr>
                        <td>{{ $guardian->name }}</td>
                        <td>{{ $guardian->relationship_to_student }}</td>
                        <td>{{ $guardian->contact_info }}</td>
                        <td>
                            <a class="btn btn-warning" href="#" data-bs-toggle="modal" data-bs-target="#editGuardian{{ $guardian->id }}">Edit</a>
                            <a class="btn btn-danger" href ="#" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $guardian->id }}">Delete</a>

                            <!-- Edit Guardian Modal -->
                            <div class="modal fade" id="editGuardian{{ $guardian->id }}" tabindex="-1" aria-labelledby="editGuardianLabel{{ $guardian->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editGuardianLabel{{ $guardian->id }}">Edit Guardian</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('guardians.update', $guardian->id) }}" method="post">
                                                @csrf
                                                @method('PUT')
                                                <label for="name_{{ $guardian->id }}" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="name_{{ $guardian->id }}" name="name" value="{{ $guardian->name }}">
                                                <label for="relationship_{{ $guardian->id }}" class="form-label">Relationship</label>
                                                <input type="text" class="form-control" id="relationship_{{ $guardian->id }}" name="relationship" value="{{ $guardian->relationship_to_student }}">
                                                <label for="phone_number_{{ $guardian->id }}" class="form-label">Phone Number</label>
                                                <input type="text" class="form-control" id="phone_number_{{ $guardian->id }}" name="phone_number" value="{{ $guardian->contact_info }}">
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
                            <div class="modal fade" id="deleteModal{{ $guardian->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $guardian->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel{{ $guardian->id }}">Confirm Deletion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete "{{ $guardian->name }}"?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="{{ route('guardians.destroy', $guardian->id) }}" method="post">
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
            {{ $guardians->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
<!-- Create Guardian Modal -->
<div class="modal fade" id="createGuardian" tabindex="-1" aria-labelledby="createGuardianLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createGuardianLabel">Add New Guardian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('guardians.store') }}" method="post">
                    @csrf
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name">
                    <label for="relationship" class="form-label">Relationship</label>
                    <input type="text" class="form-control" id="relationship" name="relationship">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Guardian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
