<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Web Development',       'slug' => 'web-development',       'description' => 'HTML, CSS, JavaScript, PHP, Node.js and modern frameworks',    'status' => 'active'],
            ['name' => 'Mobile Development',    'slug' => 'mobile-development',    'description' => 'iOS, Android, React Native and Flutter',                       'status' => 'active'],
            ['name' => 'Data Science',          'slug' => 'data-science',          'description' => 'Python, R, machine learning and data analysis',                 'status' => 'active'],
            ['name' => 'Cloud & DevOps',        'slug' => 'cloud-devops',          'description' => 'AWS, Azure, Docker, Kubernetes and CI/CD pipelines',            'status' => 'active'],
            ['name' => 'Cybersecurity',         'slug' => 'cybersecurity',         'description' => 'Ethical hacking, network security and penetration testing',     'status' => 'active'],
            ['name' => 'UI/UX Design',          'slug' => 'ui-ux-design',          'description' => 'Figma, Adobe XD, user research and interface design',           'status' => 'active'],
            ['name' => 'Business & Management', 'slug' => 'business-management',   'description' => 'Project management, leadership and entrepreneurship',           'status' => 'active'],
            ['name' => 'Digital Marketing',     'slug' => 'digital-marketing',     'description' => 'SEO, social media, content marketing and analytics',            'status' => 'active'],
            ['name' => 'Artificial Intelligence','slug' => 'artificial-intelligence','description' => 'Deep learning, NLP, computer vision and AI tools',            'status' => 'active'],
            ['name' => 'Database & SQL',        'slug' => 'database-sql',          'description' => 'MySQL, PostgreSQL, MongoDB and database design',                'status' => 'active'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
