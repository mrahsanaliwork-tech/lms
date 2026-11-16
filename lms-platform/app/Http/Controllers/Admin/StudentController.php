<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Student::with(['user', 'organization'])
            ->when($request->search, fn ($q, $s) =>
                $q->whereHas('user', fn ($u) =>
                    $u->where('name', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%")
                )->orWhere('student_id', 'like', "%{$s}%")
            )
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->batch,  fn ($q, $b) => $q->where('batch', $b))
            ->latest();

        $students = $query->paginate(15)->withQueryString()->through(fn ($s) => [
            'id'          => $s->id,
            'student_id'  => $s->student_id,
            'name'        => $s->user?->name,
            'email'       => $s->user?->email,
            'avatar'      => $s->user?->avatar,
            'phone'       => $s->user?->phone,
            'organization'=> $s->organization?->name,
            'batch'       => $s->batch,
            'group'       => $s->group,
            'status'      => $s->status,
            'enrolled_at' => $s->enrollment_date?->format('M d, Y'),
            'created_at'  => $s->created_at->format('M d, Y'),
        ]);

        $batches = Student::distinct()->whereNotNull('batch')->pluck('batch');

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'batches'  => $batches,
            'filters'  => $request->only('search', 'status', 'batch'),
        ]);
    }

    public function show(Student $student): Response
    {
        $student->load(['user', 'organization', 'enrollments.course', 'certificates', 'quizAttempts.quiz']);

        $stats = [
            'enrollments'  => $student->enrollments->count(),
            'completed'    => $student->enrollments->where('status', 'completed')->count(),
            'certificates' => $student->certificates->count(),
            'avg_progress' => $student->enrollments->avg('progress') ?? 0,
        ];

        return Inertia::render('Admin/Students/Show', [
            'student'     => [
                'id'            => $student->id,
                'student_id'    => $student->student_id,
                'name'          => $student->user?->name,
                'email'         => $student->user?->email,
                'phone'         => $student->user?->phone,
                'avatar'        => $student->user?->avatar,
                'status'        => $student->status,
                'batch'         => $student->batch,
                'group'         => $student->group,
                'organization'  => $student->organization?->name,
                'enrolled_at'   => $student->enrollment_date?->format('M d, Y'),
                'last_login'    => $student->user?->last_login_at?->diffForHumans() ?? 'Never',
                'created_at'    => $student->created_at->format('M d, Y'),
            ],
            'stats'       => $stats,
            'enrollments' => $student->enrollments->map(fn ($e) => [
                'id'       => $e->id,
                'course'   => $e->course?->title,
                'status'   => $e->status,
                'progress' => $e->progress,
                'enrolled' => $e->enrolled_at?->format('M d, Y'),
            ]),
            'certificates' => $student->certificates->map(fn ($c) => [
                'id'         => $c->id,
                'course'     => $c->course?->title,
                'number'     => $c->certificate_number,
                'issued_at'  => $c->issued_at?->format('M d, Y'),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Students/Create', [
            'organizations' => Organization::active()->get(['id', 'name']),
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
            'status'   => 'active',
        ]);
        $user->assignRole('Student');

        Student::create([
            'user_id'         => $user->id,
            'student_id'      => 'STU-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            'organization_id' => $request->organization_id,
            'batch'           => $request->batch,
            'group'           => $request->group,
            'enrollment_date' => $request->enrollment_date,
            'status'          => $request->status,
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student created successfully.');
    }

    public function edit(Student $student): Response
    {
        $student->load('user', 'organization');

        return Inertia::render('Admin/Students/Edit', [
            'student' => [
                'id'              => $student->id,
                'name'            => $student->user?->name,
                'email'           => $student->user?->email,
                'phone'           => $student->user?->phone,
                'organization_id' => $student->organization_id,
                'batch'           => $student->batch,
                'group'           => $student->group,
                'enrollment_date' => $student->enrollment_date?->format('Y-m-d'),
                'status'          => $student->status,
            ],
            'organizations' => Organization::active()->get(['id', 'name']),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $student->update([
            'organization_id' => $request->organization_id,
            'batch'           => $request->batch,
            'group'           => $request->group,
            'enrollment_date' => $request->enrollment_date,
            'status'          => $request->status,
        ]);

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student removed.');
    }

    public function bulkImport(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $csv = Reader::createFromPath($request->file('file')->getPathname(), 'r');
        $csv->setHeaderOffset(0);

        $imported = 0;
        $errors   = [];

        DB::transaction(function () use ($csv, &$imported, &$errors) {
            foreach ($csv->getRecords() as $i => $row) {
                try {
                    if (User::where('email', $row['email'])->exists()) {
                        $errors[] = "Row " . ($i + 2) . ": {$row['email']} already exists.";
                        continue;
                    }

                    $user = User::create([
                        'name'     => $row['name'],
                        'email'    => $row['email'],
                        'password' => Hash::make($row['password'] ?? 'password'),
                        'phone'    => $row['phone'] ?? null,
                        'status'   => 'active',
                    ]);
                    $user->assignRole('Student');

                    Student::create([
                        'user_id'         => $user->id,
                        'student_id'      => 'STU-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                        'batch'           => $row['batch'] ?? null,
                        'group'           => $row['group'] ?? null,
                        'enrollment_date' => !empty($row['enrollment_date']) ? $row['enrollment_date'] : null,
                        'status'          => $row['status'] ?? 'active',
                    ]);

                    $imported++;
                } catch (\Throwable $e) {
                    $errors[] = "Row " . ($i + 2) . ": " . $e->getMessage();
                }
            }
        });

        $message = "{$imported} students imported.";
        if ($errors) $message .= ' ' . count($errors) . ' rows skipped.';

        return redirect()->route('admin.students.index')->with('success', $message);
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $students = Student::with('user', 'organization')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->streamDownload(function () use ($students) {
            $csv = Writer::createFromFileObject(new SplTempFileObject());
            $csv->insertOne(['Student ID', 'Name', 'Email', 'Phone', 'Organization', 'Batch', 'Group', 'Status', 'Enrolled At', 'Created At']);

            foreach ($students as $s) {
                $csv->insertOne([
                    $s->student_id,
                    $s->user?->name,
                    $s->user?->email,
                    $s->user?->phone,
                    $s->organization?->name,
                    $s->batch,
                    $s->group,
                    $s->status,
                    $s->enrollment_date?->format('Y-m-d'),
                    $s->created_at->format('Y-m-d'),
                ]);
            }

            echo $csv->toString();
        }, 'students_' . now()->format('Y-m-d') . '.csv', $headers);
    }
}
