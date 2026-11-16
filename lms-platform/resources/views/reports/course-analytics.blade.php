<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
h1 { font-size: 16px; margin-bottom: 4px; }
p.sub { color: #666; margin-bottom: 12px; }
table { width: 100%; border-collapse: collapse; }
th { background: #8b5cf6; color: #fff; text-align: left; padding: 6px 8px; font-size: 10px; }
td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
tr:nth-child(even) td { background: #f9fafb; }
</style>
</head>
<body>
<h1>Course Analytics Report</h1>
<p class="sub">Period: {{ $from }} – {{ $to }}</p>
<table>
<thead><tr>
<th>Course</th><th>Instructor</th><th>Enrollments</th><th>Completed</th><th>Completion Rate</th><th>Avg Progress</th>
</tr></thead>
<tbody>
@foreach($rows as $r)
<tr>
<td>{{ $r['title'] }}</td><td>{{ $r['instructor'] }}</td><td>{{ $r['enrollments'] }}</td>
<td>{{ $r['completed'] }}</td><td>{{ $r['completion_rate'] }}%</td><td>{{ $r['avg_progress'] }}%</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
