<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $sarah   = Instructor::whereHas('user', fn ($q) => $q->where('email', 'sarah@lms.com'))->first();
        $michael = Instructor::whereHas('user', fn ($q) => $q->where('email', 'michael@lms.com'))->first();
        $alex    = Instructor::whereHas('user', fn ($q) => $q->where('email', 'alex@lms.com'))->first();

        $webCat    = Category::where('slug', 'web-development')->first();
        $dataCat   = Category::where('slug', 'data-science')->first();
        $mobileCat = Category::where('slug', 'mobile-development')->first();
        $aiCat     = Category::where('slug', 'artificial-intelligence')->first();
        $dbCat     = Category::where('slug', 'database-sql')->first();

        $courses = [
            [
                'title'           => 'Laravel 12 Mastery: Build Modern Web Apps',
                'description'     => 'Master Laravel from zero to hero. Build a complete SaaS application with authentication, payments, queues, and APIs.',
                'instructor'      => $sarah,
                'category'        => $webCat,
                'price'           => 79.99,
                'is_free'         => false,
                'level'           => 'intermediate',
                'language'        => 'en',
                'duration'        => 1200,
                'status'          => 'published',
                'approval_status' => 'approved',
                'modules' => [
                    [
                        'title' => 'Getting Started',
                        'lessons' => [
                            ['title' => 'Introduction to Laravel 12', 'type' => 'video', 'duration' => 15, 'is_free' => true],
                            ['title' => 'Installing & Configuring Laravel', 'type' => 'video', 'duration' => 20, 'is_free' => true],
                            ['title' => 'Understanding MVC Architecture', 'type' => 'text', 'duration' => 10, 'is_free' => false],
                        ],
                    ],
                    [
                        'title' => 'Routing & Controllers',
                        'lessons' => [
                            ['title' => 'Route Types and Parameters', 'type' => 'video', 'duration' => 25, 'is_free' => false],
                            ['title' => 'Resource Controllers', 'type' => 'video', 'duration' => 30, 'is_free' => false],
                            ['title' => 'Form Requests & Validation', 'type' => 'video', 'duration' => 20, 'is_free' => false],
                        ],
                    ],
                    [
                        'title' => 'Eloquent ORM',
                        'lessons' => [
                            ['title' => 'Models and Migrations', 'type' => 'video', 'duration' => 35, 'is_free' => false],
                            ['title' => 'Relationships Deep Dive', 'type' => 'video', 'duration' => 40, 'is_free' => false],
                            ['title' => 'Query Scopes & Factories', 'type' => 'video', 'duration' => 25, 'is_free' => false],
                        ],
                    ],
                ],
                'quiz' => [
                    'title' => 'Laravel Fundamentals Quiz',
                    'passing_marks' => 60,
                    'time_limit' => 20,
                    'questions' => [
                        ['question' => 'Which command creates a new Laravel controller?', 'type' => 'mcq', 'options' => ['php artisan make:controller','php artisan create:controller','laravel make:controller','php make:controller'], 'correct_answer' => 'php artisan make:controller', 'marks' => 2],
                        ['question' => 'What does MVC stand for?', 'type' => 'mcq', 'options' => ['Model View Controller','Module View Component','Main View Class','Module Value Controller'], 'correct_answer' => 'Model View Controller', 'marks' => 2],
                        ['question' => 'Laravel uses Eloquent as its ORM.', 'type' => 'truefalse', 'options' => ['True','False'], 'correct_answer' => 'True', 'marks' => 1],
                        ['question' => 'Which file contains route definitions for web pages?', 'type' => 'mcq', 'options' => ['routes/web.php','routes/api.php','app/routes.php','config/routes.php'], 'correct_answer' => 'routes/web.php', 'marks' => 2],
                        ['question' => 'What is the default database ORM in Laravel?', 'type' => 'mcq', 'options' => ['Eloquent','Doctrine','Active Record','Propel'], 'correct_answer' => 'Eloquent', 'marks' => 1],
                    ],
                ],
            ],
            [
                'title'           => 'Python for Data Science & Machine Learning',
                'description'     => 'Complete guide to data analysis, visualization, and building ML models with Python, NumPy, Pandas and scikit-learn.',
                'instructor'      => $michael,
                'category'        => $dataCat,
                'price'           => 89.99,
                'is_free'         => false,
                'level'           => 'beginner',
                'language'        => 'en',
                'duration'        => 1500,
                'status'          => 'published',
                'approval_status' => 'approved',
                'modules' => [
                    [
                        'title' => 'Python Foundations',
                        'lessons' => [
                            ['title' => 'Python Setup and Environment', 'type' => 'video', 'duration' => 20, 'is_free' => true],
                            ['title' => 'Variables, Types & Control Flow', 'type' => 'video', 'duration' => 30, 'is_free' => true],
                            ['title' => 'Functions and OOP', 'type' => 'video', 'duration' => 35, 'is_free' => false],
                        ],
                    ],
                    [
                        'title' => 'Data Analysis with Pandas',
                        'lessons' => [
                            ['title' => 'DataFrames and Series', 'type' => 'video', 'duration' => 40, 'is_free' => false],
                            ['title' => 'Data Cleaning Techniques', 'type' => 'video', 'duration' => 35, 'is_free' => false],
                            ['title' => 'Groupby and Aggregation', 'type' => 'video', 'duration' => 30, 'is_free' => false],
                        ],
                    ],
                    [
                        'title' => 'Machine Learning Basics',
                        'lessons' => [
                            ['title' => 'Supervised vs Unsupervised Learning', 'type' => 'text', 'duration' => 15, 'is_free' => false],
                            ['title' => 'Linear Regression with scikit-learn', 'type' => 'video', 'duration' => 45, 'is_free' => false],
                            ['title' => 'Model Evaluation and Metrics', 'type' => 'video', 'duration' => 40, 'is_free' => false],
                        ],
                    ],
                ],
                'quiz' => [
                    'title' => 'Python Basics Quiz',
                    'passing_marks' => 60,
                    'time_limit' => 15,
                    'questions' => [
                        ['question' => 'Which library is used for data manipulation in Python?', 'type' => 'mcq', 'options' => ['Pandas','NumPy','Matplotlib','SciPy'], 'correct_answer' => 'Pandas', 'marks' => 2],
                        ['question' => 'Python is a compiled language.', 'type' => 'truefalse', 'options' => ['True','False'], 'correct_answer' => 'False', 'marks' => 1],
                        ['question' => 'What does iloc stand for in Pandas?', 'type' => 'mcq', 'options' => ['Integer Location','Index Location','In-place Location','Index List'], 'correct_answer' => 'Integer Location', 'marks' => 2],
                        ['question' => 'Which algorithm is used for classification?', 'type' => 'mcq', 'options' => ['Logistic Regression','Linear Regression','K-Means','PCA'], 'correct_answer' => 'Logistic Regression', 'marks' => 2],
                        ['question' => 'Pandas is built on top of NumPy.', 'type' => 'truefalse', 'options' => ['True','False'], 'correct_answer' => 'True', 'marks' => 1],
                    ],
                ],
            ],
            [
                'title'           => 'React Native: Build Cross-Platform Mobile Apps',
                'description'     => 'Learn to build production-ready iOS and Android apps with React Native, Expo, and modern navigation patterns.',
                'instructor'      => $alex,
                'category'        => $mobileCat,
                'price'           => 69.99,
                'is_free'         => false,
                'level'           => 'intermediate',
                'language'        => 'en',
                'duration'        => 1000,
                'status'          => 'published',
                'approval_status' => 'approved',
                'modules' => [
                    [
                        'title' => 'React Native Fundamentals',
                        'lessons' => [
                            ['title' => 'Setting Up Expo & Development Environment', 'type' => 'video', 'duration' => 25, 'is_free' => true],
                            ['title' => 'Core Components: View, Text, Image', 'type' => 'video', 'duration' => 30, 'is_free' => false],
                            ['title' => 'StyleSheet and Flexbox Layout', 'type' => 'video', 'duration' => 35, 'is_free' => false],
                        ],
                    ],
                    [
                        'title' => 'State & Navigation',
                        'lessons' => [
                            ['title' => 'useState and useEffect Hooks', 'type' => 'video', 'duration' => 40, 'is_free' => false],
                            ['title' => 'React Navigation Setup', 'type' => 'video', 'duration' => 35, 'is_free' => false],
                            ['title' => 'Stack, Tab & Drawer Navigation', 'type' => 'video', 'duration' => 45, 'is_free' => false],
                        ],
                    ],
                ],
                'quiz' => [
                    'title' => 'React Native Concepts Quiz',
                    'passing_marks' => 60,
                    'time_limit' => 10,
                    'questions' => [
                        ['question' => 'Which command initialises a new Expo project?', 'type' => 'mcq', 'options' => ['expo init','npx create-expo-app','react-native init','expo create'], 'correct_answer' => 'npx create-expo-app', 'marks' => 2],
                        ['question' => 'React Native compiles to native code.', 'type' => 'truefalse', 'options' => ['True','False'], 'correct_answer' => 'True', 'marks' => 1],
                        ['question' => 'Which hook manages component state?', 'type' => 'mcq', 'options' => ['useState','useEffect','useContext','useRef'], 'correct_answer' => 'useState', 'marks' => 2],
                    ],
                ],
            ],
            [
                'title'           => 'Introduction to AI & Prompt Engineering',
                'description'     => 'Understand large language models, build AI-powered tools, and master the art of prompt engineering.',
                'instructor'      => $michael,
                'category'        => $aiCat,
                'price'           => 0,
                'is_free'         => true,
                'level'           => 'beginner',
                'language'        => 'en',
                'duration'        => 600,
                'status'          => 'published',
                'approval_status' => 'approved',
                'modules' => [
                    [
                        'title' => 'Understanding AI & LLMs',
                        'lessons' => [
                            ['title' => 'What are Large Language Models?', 'type' => 'video', 'duration' => 20, 'is_free' => true],
                            ['title' => 'Tokens, Context Windows and Temperature', 'type' => 'text', 'duration' => 15, 'is_free' => true],
                            ['title' => 'GPT vs Claude vs Gemini Comparison', 'type' => 'video', 'duration' => 25, 'is_free' => true],
                        ],
                    ],
                    [
                        'title' => 'Prompt Engineering Techniques',
                        'lessons' => [
                            ['title' => 'Zero-shot and Few-shot Prompting', 'type' => 'video', 'duration' => 30, 'is_free' => true],
                            ['title' => 'Chain-of-Thought Reasoning', 'type' => 'video', 'duration' => 25, 'is_free' => true],
                            ['title' => 'Building an AI-Powered App', 'type' => 'video', 'duration' => 40, 'is_free' => true],
                        ],
                    ],
                ],
                'quiz' => [
                    'title' => 'AI Fundamentals Quiz',
                    'passing_marks' => 50,
                    'time_limit' => 10,
                    'questions' => [
                        ['question' => 'What does LLM stand for?', 'type' => 'mcq', 'options' => ['Large Language Model','Linear Learning Method','Linked List Machine','Long Logic Module'], 'correct_answer' => 'Large Language Model', 'marks' => 2],
                        ['question' => 'Prompt engineering can significantly improve AI output quality.', 'type' => 'truefalse', 'options' => ['True','False'], 'correct_answer' => 'True', 'marks' => 1],
                        ['question' => 'What is a "token" in the context of LLMs?', 'type' => 'mcq', 'options' => ['A unit of text (word or subword)','A database key','A login credential','A training epoch'], 'correct_answer' => 'A unit of text (word or subword)', 'marks' => 2],
                    ],
                ],
            ],
            [
                'title'           => 'SQL & Database Design Masterclass',
                'description'     => 'Master SQL from basic queries to advanced indexing, stored procedures and database optimisation.',
                'instructor'      => $sarah,
                'category'        => $dbCat,
                'price'           => 49.99,
                'is_free'         => false,
                'level'           => 'beginner',
                'language'        => 'en',
                'duration'        => 900,
                'status'          => 'published',
                'approval_status' => 'approved',
                'modules' => [
                    [
                        'title' => 'SQL Fundamentals',
                        'lessons' => [
                            ['title' => 'What is a Relational Database?', 'type' => 'video', 'duration' => 20, 'is_free' => true],
                            ['title' => 'SELECT, WHERE, ORDER BY', 'type' => 'video', 'duration' => 30, 'is_free' => true],
                            ['title' => 'INSERT, UPDATE, DELETE', 'type' => 'video', 'duration' => 25, 'is_free' => false],
                        ],
                    ],
                    [
                        'title' => 'Joins & Relationships',
                        'lessons' => [
                            ['title' => 'INNER, LEFT, RIGHT, FULL JOIN', 'type' => 'video', 'duration' => 35, 'is_free' => false],
                            ['title' => 'Subqueries and CTEs', 'type' => 'video', 'duration' => 30, 'is_free' => false],
                            ['title' => 'Database Normalisation (1NF–3NF)', 'type' => 'text', 'duration' => 20, 'is_free' => false],
                        ],
                    ],
                    [
                        'title' => 'Performance & Indexing',
                        'lessons' => [
                            ['title' => 'Understanding Query Execution Plans', 'type' => 'video', 'duration' => 35, 'is_free' => false],
                            ['title' => 'Index Types and When to Use Them', 'type' => 'video', 'duration' => 30, 'is_free' => false],
                        ],
                    ],
                ],
                'quiz' => [
                    'title' => 'SQL Essentials Quiz',
                    'passing_marks' => 60,
                    'time_limit' => 15,
                    'questions' => [
                        ['question' => 'Which SQL clause filters results?', 'type' => 'mcq', 'options' => ['WHERE','FILTER','HAVING','SELECT'], 'correct_answer' => 'WHERE', 'marks' => 1],
                        ['question' => 'An INNER JOIN returns all rows from both tables.', 'type' => 'truefalse', 'options' => ['True','False'], 'correct_answer' => 'False', 'marks' => 1],
                        ['question' => 'Which aggregate function counts rows?', 'type' => 'mcq', 'options' => ['COUNT(*)','SUM()','MAX()','AVG()'], 'correct_answer' => 'COUNT(*)', 'marks' => 2],
                        ['question' => 'What does ACID stand for in databases?', 'type' => 'mcq', 'options' => ['Atomicity Consistency Isolation Durability','Access Control Identity Data','Abstract Class Interface Design','Asynchronous Concurrent Indexed Data'], 'correct_answer' => 'Atomicity Consistency Isolation Durability', 'marks' => 2],
                    ],
                ],
            ],
        ];

        foreach ($courses as $data) {
            if (!$data['instructor'] || !$data['category']) continue;

            $slug = Str::slug($data['title']) . '-' . Str::random(5);

            $course = Course::firstOrCreate(
                ['title' => $data['title']],
                [
                    'slug'            => $slug,
                    'description'     => $data['description'],
                    'instructor_id'   => $data['instructor']->id,
                    'category_id'     => $data['category']->id,
                    'price'           => $data['price'],
                    'is_free'         => $data['is_free'],
                    'level'           => $data['level'],
                    'language'        => $data['language'],
                    'duration'        => $data['duration'],
                    'status'          => $data['status'],
                    'approval_status' => $data['approval_status'],
                    'approved_by'     => 1,
                    'approved_at'     => now(),
                    'published_at'    => now()->subDays(rand(1, 60)),
                ]
            );

            // Modules + Lessons
            foreach ($data['modules'] as $modOrder => $modData) {
                $module = CourseModule::firstOrCreate(
                    ['course_id' => $course->id, 'title' => $modData['title']],
                    ['order' => $modOrder + 1, 'status' => 'active']
                );

                foreach ($modData['lessons'] as $lessonOrder => $lessonData) {
                    Lesson::firstOrCreate(
                        ['module_id' => $module->id, 'title' => $lessonData['title']],
                        [
                            'type'     => $lessonData['type'],
                            'duration' => $lessonData['duration'],
                            'order'    => $lessonOrder + 1,
                            'is_free'  => $lessonData['is_free'],
                            'status'   => 'active',
                        ]
                    );
                }
            }

            // Quiz
            if (!empty($data['quiz'])) {
                $quiz = Quiz::firstOrCreate(
                    ['course_id' => $course->id, 'title' => $data['quiz']['title']],
                    [
                        'passing_marks'    => $data['quiz']['passing_marks'],
                        'time_limit'       => $data['quiz']['time_limit'],
                        'attempts_allowed' => 3,
                        'is_randomized'    => false,
                        'status'           => 'published',
                    ]
                );

                foreach ($data['quiz']['questions'] as $q) {
                    QuizQuestion::firstOrCreate(
                        ['quiz_id' => $quiz->id, 'question' => $q['question']],
                        [
                            'type'           => $q['type'],
                            'options'        => $q['options'],
                            'correct_answer' => $q['correct_answer'],
                            'marks'          => $q['marks'],
                        ]
                    );
                }
            }
        }
    }
}
