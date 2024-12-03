

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
</head>
<body>
    <h1>Attendance Report</h1>
    <h3>From: {{ $startDate ?? 'N/A' }} To: {{ $endDate ?? 'N/A' }}</h3>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Section</th>
                <th>Total Absent</th>
                <th>Total Present</th>
                <th>School Year</th>
                <th>Average Present (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->student->name }}</td>
                    <td>{{ $attendance->student->section->grade }}-{{ $attendance->student->section->section }}</td>
                    <td>{{ $attendance->total_absent }}</td>
                    <td>{{ $attendance->total_present }}</td>
                    <td>{{ $attendance->student->SchoolYear->year }}</td>
                    <td>{{ number_format($attendance->average_days_present * 100, 2) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
