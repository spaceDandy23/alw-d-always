@extends('layouts.master')

@section('content')
@if ($activeSchoolYear)

    @include('partials.alerts')

    <button type="button" class="btn btn-secondary mb-4" data-bs-toggle="modal" data-bs-target="#changeSchoolYearModal">
        Change School Year
    </button>
    <button type="button" class="btn btn-warning mb-4" data-bs-toggle="modal" data-bs-target="#cancelAttendanceModal">
        Cancel Attendance For Today
    </button>
    <div class="modal fade" id="cancelAttendanceModal" tabindex="-1" aria-labelledby="cancelAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelAttendanceModalLabel">Cancel Attendance for Today</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('cancel.class.session') }}" method="POST">
                <div class="modal-body">
                    <p>Choose which sessions to cancel attendance for:</p>
                        @csrf 
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="cancel_morning" value="1" id="cancel_morning">
                            <label class="form-check-label" for="cancel_morning">
                                Cancel Morning Session
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="cancel_lunch" value="1" id="cancel_lunch">
                            <label class="form-check-label" for="cancel_lunch">
                                Cancel Lunch Session
                            </label>
                        </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

        <!-- Change School Year Modal -->
    <div class="modal fade" id="changeSchoolYearModal" tabindex="-1" aria-labelledby="changeSchoolYearModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeSchoolYearModalLabel">Change School Year</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changeSchoolYearForm" action="{{ route('change.school.year') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="new_school_year" class="form-label">Select New School Year</label>
                            <select class="form-select" id="new_school_year" name="new_school_year" required>
                                <option selected disabled>Choose School Year</option>
                                @foreach ($schoolYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="changeSchoolYearForm" class="btn btn-primary">Change</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Button to Open Modal -->
    <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#schoolYearModal">
    Back up database
    </button>

    <!-- School Year Input Modal -->
    <div class="modal fade" id="schoolYearModal" tabindex="-1" aria-labelledby="schoolYearModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="schoolYearModalLabel">Back Up Database</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <strong>Important Warning!</strong><br>
                        If you need to import this backup later or sooner, please contact the developers for assistance.
                    </div>
                    <form id="schoolYearForm" action="{{ route('back.up') }}" method="POST">
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="schoolYearForm" class="btn btn-primary">Save Backup</button>
                </div>
            </div>
        </div>
    </div>
    <h1 class="my-4 text-center display-4">Dashboard</h1>

        <!-- Recent Attendance Records -->
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

    <!-- Overall Attendance Summary -->
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-primary">Overall Attendance Summary</h5>
            <div class="row">
                <div class="col-md-6">
                    <p>School Year: <span id="school_year">{{ $overallAttendanceSummary['activeSchoolYear'] }}</span></p>
                    <p>Total Students this school year: <span id="total-students">{{ $overallAttendanceSummary['totalStudents'] }}</span></p>
                </div>
                <div class="col-md-6">
                    <p>Total Days Recorded: <span id="total-days">{{ $overallAttendanceSummary['totalDaysRecorded'] }}</span></p>
                    <p>Average Attendance Rate Among All Students: <span id="attendance-rate">{{ $overallAttendanceSummary['overallAverageAttendanceRate'] }} %</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Breakdown -->
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-primary">Attendance Breakdown</h5>
            <canvas id="monthy_attendance_chart" class="mb-4" width="300" height="150"></canvas>

            <hr class="my-4">

            <h5 class="card-title text-center fw-bold text-uppercase text-success">Students With Perfect Attendance</h5>
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

            <hr class="my-4">

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
                                {{ $absent->student->name }} 
                                <span class="badge bg-warning">Total Absent: {{ $absent->total_absent }}</span>
                                <label>
                                    <input type="checkbox" name="absents[]" value="{{ $absent->student->id }}">
                                </label>
                            </div>
                            <a href="{{ route('student.profile', $absent->student->id) }}" class="btn btn-outline-primary btn-sm">View Profile</a>
                        </li>
                    @endforeach
                </ul>
                <div class="d-flex justify-content-center mt-3">
                    {{ $absentAlot->links('vendor.pagination.bootstrap-5') }}
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Send Message</button>
            </form>
            @endif
        </div>
    </div>



    <!-- Attendance by Grade and Section -->
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-primary">Attendance by Grade and Section</h5>
            <canvas id="monthly_attendance_chart_per_section" class="mb-4" width="300" height="150"></canvas>
            <div class="row text-center">
                @foreach ($attendanceBySection as $sectionData)
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm border-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary">{{ $sectionData->section_name}}</h6>
                                <p class="card-text"><strong>Total Attendance:</strong> {{ round($sectionData->section_overall * 100,2) }}%</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Attendance Trends Over Time -->
    <div class="card mb-4 shadow-lg border-0">
        <div class="card-body bg-light">
            <h5 class="card-title text-center fw-bold text-uppercase text-primary">Attendance Trends Over Time</h5>
            <canvas id="attendance_trends_over_time" width="400" height="200"></canvas>
        </div>
    </div>
@else

<h3>No active school year</h3>


@endif
<script>

    document.addEventListener('DOMContentLoaded', ()=> {


        console.log(@json($activeSchoolYear));




        attendanceTrend(@json($attendanceTrend ?? []));

        generateChartForMonthlyAttendance(@json($attendancePerMonth ?? []));


        generateChartForSection(@json($attendanceBySection ?? []));



        


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
                return `${data.section_name}`;

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
            console.log("Raw attendance trend data:", attendanceTrend);

            let cleanedAttendanceData = {};
            let cleanedDateSet = new Set();
            attendanceTrend.forEach((data) => {
                const monthYear = `${data.year}/${data.month.toString().padStart(2, '0')}`;
                cleanedDateSet.add(monthYear);
            });

            const cleanedArrayDate = Array.from(cleanedDateSet).sort(); 
            console.log("Unique sorted dates:", cleanedArrayDate);
            attendanceTrend.forEach((data) => {
                const key = data.section_name;
                if (!cleanedAttendanceData[key]) {
                    cleanedAttendanceData[key] = new Array(cleanedArrayDate.length).fill(null);
                }

                const monthYear = `${data.year}/${data.month.toString().padStart(2, '0')}`;
                const index = cleanedArrayDate.indexOf(monthYear);
                if (index !== -1) {
                    cleanedAttendanceData[key][index] = parseFloat(data.total_present);
                }
            });

            console.log("Cleaned attendance data:", cleanedAttendanceData);
            const dataSets = Object.entries(cleanedAttendanceData).map(([key, data]) => ({
                label: key,
                data: data,
                borderColor: getRandomColor(),
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
            }));

            console.log("Datasets for Chart.js:", dataSets);
            const ctx = document.getElementById('attendance_trends_over_time').getContext('2d');
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
                            position: 'top',
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
                                text: 'Total Attendance',
                            },
                            beginAtZero: true,
                        },
                    },
                },
            });
        }
        function getRandomColor() {
            const random = () => Math.floor(Math.random() * 255);
            return `rgba(${random()}, ${random()}, ${random()}, 1)`;
        }





    });

</script>
@endsection
