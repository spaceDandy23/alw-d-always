@extends('layouts.master')

@section('page_title', 'Users')

@section('content')

<div class="row justify-content-center">
    <div class="col">
        <table class="table table-striped">
        <div class="d-flex justify-content-center">
            <a href="#" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#createUser">Add User</a>
        </div>
            <thead>
                @include('partials.alerts')
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                        {{ ucfirst($user->role) }}
                    
                        </td>
                        <td>
                            <a class="btn btn-warning" href="#" data-bs-toggle="modal" data-bs-target="#editUser{{ $user->id }}">Edit</a>
                            @if (Auth::id() !== $user->id)
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id }}">
                                Delete
                            </button>
                            @endif
                            <!-- Edit User Modal -->
                            <div class="modal fade" id="editUser{{ $user->id }}" tabindex="-1" aria-labelledby="editUserLabel{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editUserLabel{{ $user->id }}">Edit User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('users.update',$user->id) }}" method="post">
                                                @csrf
                                                @method('PUT')
                                                <label for="name_{{ $user->id }}" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="name_{{ $user->id }}" name="name" value="{{ $user->name }}" >
                                                <label for="email_{{ $user->id }}" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email_{{ $user->id }}" name="email" value="{{ $user->email }}" >
                                                <label for="password_{{ $user->id }}" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="password_{{ $user->id }}" name="password" >
                                                @if(Auth::id() !== $user->id)
                                                <label for="role_{{ $user->id }}" class="form-label">Role</label>
                                                <select class="form-select" name="role" id="role_{{ $user->id }}">
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role }}" {{ $user->role === $role ? "selected" :  "" }}> {{ucfirst($role)}}</option>
                                                    @endforeach
                                                </select>
                                                @endif
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Delete User Modal -->
                            <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteUserModalLabel{{ $user->id }}">Confirm Deletion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete "{{ $user->name }}"?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="{{route('users.destroy', $user->id)}}" method="post">
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
            {{ $users->links('vendor.pagination.bootstrap-5')  }}
        </div>
    </div>
</div>
<!-- Create User Modal -->
<div class="modal fade" id="createUser" tabindex="-1" aria-labelledby="createUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('users.store') }}" method="post">
                    @csrf
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" >
                    
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email" >

                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" >

                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                    </select>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
