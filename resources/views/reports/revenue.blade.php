<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
h1 { font-size: 16px; margin-bottom: 4px; }
p.sub { color: #666; margin-bottom: 12px; }
table { width: 100%; border-collapse: collapse; }
th { background: #10b981; color: #fff; text-align: left; padding: 6px 8px; font-size: 10px; }
td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
tr:nth-child(even) td { background: #f9fafb; }
</style>
</head>
<body>
<h1>Revenue Report</h1>
<p class="sub">Period: {{ $from }} – {{ $to }}</p>
<table>
<thead><tr>
<th>Student</th><th>Course</th><th>Amount</th><th>Currency</th><th>Discount</th><th>Method</th><th>Paid At</th>
</tr></thead>
<tbody>
@foreach($rows as $r)
<tr>
<td>{{ $r['student'] }}</td><td>{{ $r['course'] }}</td><td>{{ $r['amount'] }}</td>
<td>{{ $r['currency'] }}</td><td>{{ $r['discount'] }}</td><td>{{ $r['method'] }}</td><td>{{ $r['paid_at'] }}</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
