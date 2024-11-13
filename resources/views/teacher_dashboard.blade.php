@extends('layouts.master')

@section('content')
@if ($activeSchoolYear)
    @include('partials.alerts')
    <h1 class="my-4 text-center display-4">Teacher Dashboard</h1>
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-primary">Recent Attendance Records</h5>
            @if($recentAttendanceRecords->isEmpty())
                <p class="text-muted text-center">No attendance records for today.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($recentAttendanceRecords as $record)
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <div>
                                <strong>{{ $record->student->name }}</strong> <span class="text-muted">({{ $record->date }})</span>
                            </div>
                            <div>
                                <span class="badge {{ $record->status_morning == 'present' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($record->status_morning) }}</span>
                                <span class="badge {{ $record->status_lunch == 'present' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($record->status_lunch) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="d-flex justify-content-center mt-3">
                    {{ $recentAttendanceRecords->links('vendor.pagination.bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-success">Students With Perfect Attendance From Watchlist</h5>
            @if($perfectAttendance->isEmpty())
                <p class="text-muted text-center">No students have perfect attendance.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($perfectAttendance as $student)
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            {{ $student->student->name }}
                            <span class="badge bg-success rounded-pill">Perfect Attendance</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-warning">Students with Frequent Absences From Watchlist</h5>
            @if($absentAlot->isEmpty())
                <p class="text-muted text-center">No students with more than 4 absences.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($absentAlot as $absent)
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <div>
                                {{ $absent->student->name }} 
                                <span class="badge bg-warning">Total Absent: {{ $absent->total_absent }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="d-flex justify-content-center mt-3">
                    {{ $absentAlot->links('vendor.pagination.bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-primary">By Watchlist</h5>
            <h5 class="card-title text-center fw-bold text-uppercase text-success">Attendance Breakdown by Section</h5>
            <canvas id="section_attendance_chart" class="mb-4" width="300" height="150"></canvas>

            <hr class="my-4">
            <h5 class="card-title text-center fw-bold text-uppercase text-primary">Attendance Trends Over Time</h5>
            <canvas id="attendance_trends_over_time_teacher" width="400" height="200"></canvas>
        </div>
    </div>


@else
    <h3>No active school year</h3>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {

        attendanceTrend(@json($attendanceTrend ?? []));
        generateChartForSection(@json($attendanceBySection ?? []));

        function generateChartForSection(sectionData) {
            const ctx = document.getElementById('section_attendance_chart').getContext('2d');
            let labels = sectionData.map(data => `${data.grade} - ${data.section}`);
            let totalPresents = sectionData.map(data => data.section_overall * 100);

            const sectionAttendanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Section Attendance Average (%)',
                        data: totalPresents,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function attendanceTrend(attendanceTrend) {
            const cleanedAttendanceData = {};
            const cleanedDate = {};
            const ctx = document.getElementById('attendance_trends_over_time_teacher').getContext('2d');

            attendanceTrend.forEach((data) => {
                cleanedDate[`${data.year}/${data.month < 10 ? '0' + data.month : data.month}`] = `${data.year}/${data.month < 10 ? '0' + data.month : data.month}`;
            });

            const cleanedArrayDate = Object.entries(cleanedDate).map((data) => data[1]);

            attendanceTrend.forEach((data) => {
                const key = `${data.grade}-${data.section}`;
                if (!cleanedAttendanceData[key]) {
                    cleanedAttendanceData[key] = new Array(cleanedArrayDate.length).fill(null); 
                }

                const index = cleanedArrayDate.findIndex(date => date === `${data.year}/${data.month < 10 ? '0' + data.month : data.month}`);
                if (index !== -1) {
                    cleanedAttendanceData[key][index] = data.total_present; 
                }
            });

            const dataSets = Object.entries(cleanedAttendanceData).map(([key, data]) => {
                return {
                    label: key,
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false
                };
            });

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: cleanedArrayDate,
                    datasets: dataSets
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
</script>

@endsection
