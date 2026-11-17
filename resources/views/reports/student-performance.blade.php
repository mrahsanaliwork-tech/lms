<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
h1 { font-size: 16px; margin-bottom: 4px; }
p.sub { color: #666; margin-bottom: 12px; }
table { width: 100%; border-collapse: collapse; }
th { background: #3b82f6; color: #fff; text-align: left; padding: 6px 8px; font-size: 10px; }
td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
tr:nth-child(even) td { background: #f9fafb; }
</style>
</head>
<body>
<h1>Student Performance Report</h1>
<p class="sub">Period: {{ $from }} – {{ $to }}</p>
<table>
<thead><tr>
<th>Name</th><th>Email</th><th>Courses</th><th>Completed</th><th>Avg Progress</th><th>Quiz Attempts</th><th>Submissions</th>
</tr></thead>
<tbody>
@foreach($rows as $r)
<tr>
<td>{{ $r['name'] }}</td><td>{{ $r['email'] }}</td><td>{{ $r['enrollments'] }}</td>
<td>{{ $r['completed'] }}</td><td>{{ $r['avg_progress'] }}%</td><td>{{ $r['quiz_attempts'] }}</td><td>{{ $r['submissions'] }}</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
