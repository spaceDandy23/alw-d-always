
@extends('layouts.master')

@section('content')
@if ($activeSchoolYear)
    <h2>Dashboard</h2>
    <h3>School Year: {{ $activeSchoolYear->year }}</h3>
    
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-primary">Overall Attendance Summary</h5>
            <div class="row">
                <div class="col-md-6">
                    <p>School Year: <span id="school_year">{{ $activeSchoolYear->year ?? 'N/A' }}</span></p>
                    <p>Total Students in your class: <span id="total-students">{{ $totalStudents }}</span></p>
                </div>
                <div class="col-md-6">
                    <p>Total Days Recorded: <span id="total-days">{{ $totalDaysRecorded }}</span></p>
                    <p>Average Attendance Rate Of Students In Your Class: <span id="attendance-rate">{{ number_format($attendanceRate, 2) }} %</span></p>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-warning">Students with Frequent Absences</h5>
            @if($absentAlot->isEmpty())
                <p class="text-muted text-center">No students with more than 4 absences.</p>
            @else
            <form action="{{ route('mass.message.parent') }}" method="POST">
                @csrf
                <ul class="list-group list-group-flush">
                    @foreach ($absentAlot as $absent)
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <div>
                                {{ $absent->name }} 
                                <span class="badge bg-warning">Total Absent: {{ $absent->total_absent }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </form>
            @endif
        </div>
    </div>
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-primary">Attendance Trends by Section</h5>
            <canvas id="attendance_trends_by_section" width="400" height="200"></canvas>
        </div>
    </div>

    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-primary">Overall Average Attendance Percentage by Section</h5>
            <canvas id="attendance_percentage_by_section" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const ctxTrends = document.getElementById('attendance_trends_by_section').getContext('2d');
            const ctxAvg = document.getElementById('attendance_percentage_by_section').getContext('2d');

            const attendanceTrends = @json($attendanceTrends);
            const overallAverageAttendancePercentage = @json($overallAverageAttendancePercentage);

            if (attendanceTrends) {
                const datasets = Object.keys(attendanceTrends).map(section => {
                    const sectionData = attendanceTrends[section];

                    return {
                        label: section,
                        data: sectionData.map(record => record.present),
                        borderColor: `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 1)`,
                        backgroundColor: `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.2)`,
                        borderWidth: 2,
                        fill: true,
                    };
                });


                const labels = attendanceTrends[Object.keys(attendanceTrends)[0]].map(record => record.month); 


                const lineChart = new Chart(ctxTrends, {
                    type: 'line',
                    data: {
                        labels: labels, 
                        datasets: datasets,
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month', // Label for the X-axis
                                },
                                ticks: {
                                    autoSkip: true, // Automatically skip months if the number of labels is too large
                                },
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Number of Present Students', // Y-axis title
                                },
                                beginAtZero: true, // Ensure the Y-axis starts from 0
                            },
                        },
                    },
                });
            } else {
                console.error("No attendance data available for trends.");
            }
            if (overallAverageAttendancePercentage) {
                const datasetsAvg = [{
                    label: 'Attendance Percentage',
                    data: overallAverageAttendancePercentage.map(section => section.attendance_percentage),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)', 
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                }];
                
                const labelsAvg = overallAverageAttendancePercentage.map(section => section.section_label);

                const barChart = new Chart(ctxAvg, {
                    type: 'bar',
                    data: {
                        labels: labelsAvg,
                        datasets: datasetsAvg,
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Section',
                                },
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Attendance Percentage (%)',
                                },
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 10,
                                    max: 100,
                                },
                            },
                        },
                    },
                });
            } else {
                console.error("No data available for average attendance percentage.");
            }
        });
    </script>
@else
    <h3>No active school year</h3>
@endif
@endsection
