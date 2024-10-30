@extends('layouts.master')

@section('content')
<div class="container">
    <!-- Dashboard Title -->
    <h1 class="my-4">Dashboard</h1>

    <!-- Overall Attendance Summary -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Overall Attendance Summary</h5>
            <p>School Year: <span id="school_year">{{ $overallAttendanceSummary['activeSchoolYear'] }}</span></p>
            <p>Total Students this school year: <span id="total-students">{{ $overallAttendanceSummary['totalStudents'] }}</span></p>
            <p>Total Days Recorded: <span id="total-days">{{ $overallAttendanceSummary['totalDaysRecorded'] }}</span></p>
            <p>Average Attendance Rate Among All Students: <span id="attendance-rate">{{ $overallAttendanceSummary['overallAverageAttendanceRate'] }}</span></p>
        </div>
    </div>

    <!-- Attendance Breakdown -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Attendance Breakdown</h5>
            <canvas id="monthy_attendance_chart" width="300" height="150"></canvas>
            
            <hr class="my-4">
            
            <h5 class="card-title">Students With Perfect Attendance So Far</h5>
            @if($perfectAttendance->isEmpty())
                <p class="text-muted">No students have perfect attendance.</p>
            @else
                <ul class="list-group">
                    @foreach ($perfectAttendance as $student)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $student->student->name }}
                            <span class="badge bg-success">Perfect Attendance</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            
            <hr class="my-4">
            
            <h5 class="card-title">Students with Frequent Absences</h5>
            @if($absentAlot->isEmpty())
                <p class="text-muted">No students with more than 4 absences.</p>
            @else
                <ul class="list-group">
                    @foreach ($absentAlot as $absent)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                {{ $absent->student->name }} 
                                <span class="badge bg-warning">Total Absent: {{ $absent->total_absent }}</span>
                            </div>
                            <div>
                                <a href="{{ route('student.profile', $absent->student->id) }}" class="btn btn-outline-primary btn-sm">
                                    View Profile
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="d-flex justify-content-center">
                    {{ $absentAlot->links('vendor.pagination.bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Attendance Records -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Recent Attendance Records</h5>
            
            @if($recentAttendanceRecords->isEmpty())
                <p class="text-muted">No attendance records for today.</p>
            @else
            <ul class="list-group">
                @foreach ($recentAttendanceRecords as $record)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="row">
                            <div class="col-6">
                                <strong>{{ $record->student->name }}</strong>
                                <span class="text-muted">({{ $record->date }})</span>
                            </div>
                            <div class="col-6">
                                <span class="badge {{ $record->status_morning == 'present' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($record->status_morning) }}
                                </span>
                                <span class="badge {{ $record->status_lunch == 'present' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($record->status_lunch) }}
                                </span>
                            </div>
                        </div>

                    </li>
                @endforeach
            </ul>
            <div class="d-flex justify-content-center">
                {{ $recentAttendanceRecords->links('vendor.pagination.bootstrap-5') }}
            </div>

            @endif
        </div>
    </div>


    <!-- Attendance by Grade and Section -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Attendance by Grade and Section So Far</h5>
            <canvas id="monthly_attendance_chart_per_section" width="300" height="150"></canvas>
            <div class="container my-4">
                <div class="row">
                    @foreach ($attendanceBySection as $sectionData)
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-center">{{ $sectionData->grade }} - {{ $sectionData->section }}</h5>
                                    <p class="card-text text-center">
                                        <strong>Total Attendance:</strong> {{ $sectionData->section_overall * 100}} %
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Trends Over Time -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Attendance Trends Over Time</h5>
            <canvas id="attendance_trends_over_time" width="400" height="200"></canvas>

        </div>
    </div>


</div>
<script>

    document.addEventListener('DOMContentLoaded', ()=> {





        attendanceTrend(@json($attendanceTrend));

        generateChartForMonthlyAttendance(@json($attendancePerMonth));


        generateChartForSection(@json($attendanceBySection));



        function generateChartForMonthlyAttendance(attendanceData){
            const ctx = document.getElementById('monthy_attendance_chart').getContext('2d');

            const labels = attendanceData.map(data => `${data.year}-${data.month < 10 ? '0' + data.month : data.month}`);
            const totalPresents = attendanceData.map(data => data.total_present);
            const monthlyAttendanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels, 
                    datasets: [{
                        label: 'Total Present',
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


        function generateChartForSection(sectionData){


            const ctx = document.getElementById('monthly_attendance_chart_per_section').getContext('2d');
            let labels = sectionData.map(function(data) {
                return `${data.grade} - ${data.section}`;

            });
            let totalPresents = sectionData.map(function(data){

                return data.section_overall * 100;

            });

            const monthlyAttendanceChartPerSection = new Chart(ctx, {
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
            console.log(attendanceTrend);
            let cleanedAttendanceData = {};
            let cleanedDate = {};

            const ctx = document.getElementById('attendance_trends_over_time').getContext('2d');

            attendanceTrend.forEach((data) => {
                cleanedDate[`${data.year}/${data.month < 10 ? '0' + data.month : data.month}`] = `${data.year}/${data.month < 10 ? '0' + data.month : data.month}`;
            });

            const cleanedArrayDate = Object.entries(cleanedDate).map((data) => data[1]);
            console.log(cleanedArrayDate);

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

            console.log(cleanedAttendanceData);


            const dataSets = Object.entries(cleanedAttendanceData).map(([key, data]) => {
                return {
                    label: key,
                    data: data
                };
            });

            console.log(dataSets.length);
            const attendanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: cleanedArrayDate,
                    datasets: dataSets,
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        },
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Months',
                            },
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Attendance',
                            },
                            beginAtZero: true,
                        },
                    },
                },
            });
        }

    });

</script>
@endsection