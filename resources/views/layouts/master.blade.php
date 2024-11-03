<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            min-width: 250px;
            max-width: 250px;
            background-color: #f8f9fa;
            padding: 1rem;
            border-right: 1px solid #ddd;
        }
        .sidebar a {
            display: block;
            padding: 0.75rem;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
        }
        .sidebar a:hover {
            background-color: #e9ecef;
        }
        .content {
            flex: 1;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        @php
            use App\Models\SchoolYear;
        @endphp
        <h3 class="mb-4">{{ SchoolYear::where('is_active', true)->first()->year ?? 'No school year'  }}</h3>
        <ul class="nav flex-column">
            @if(Auth::check())
                @if(Auth::user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('students.index') }}">Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('guardians.index') }}">Guardians</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logs.index') }}">Logs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('attendances.index') }}">Attendances</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('notifications.index') }}">Notifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register.student.parent') }}">Register Student</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('verify') }}">Scan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('holidays.index') }}">Special Occasions</a>
                    </li>
                @endif
            @endif
        </ul>
        <hr>
        <div class="mt-auto">
            @if(Auth::check())
                <p>Hello, {{ Auth::user()->name }}</p>
                <a class="btn btn-danger btn-sm" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
            @else
                <a class="btn btn-primary btn-sm" href="{{ route('login') }}">Login</a>
            @endif
        </div>
    </nav>

    <div class="content">
        @yield('content')
    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to log out?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="logout_form" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">Log out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
