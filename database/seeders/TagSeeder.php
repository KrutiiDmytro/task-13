<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'PHP'], ['name' => 'JavaScript'], ['name' => 'MySQL'], 
            ['name' => 'Git'], ['name' => 'Docker'], ['name' => 'API'],
            ['name' => 'Безопасность'], ['name' => 'Тестирование'], 
            ['name' => 'Веб-разработка'], ['name' => 'Программирование'],
            ['name' => 'Laravel'], ['name' => 'Symfony'], ['name' => 'React'], 
            ['name' => 'Vue.js'], ['name' => 'Node.js'], ['name' => 'Python'],
            ['name' => 'CSS'], ['name' => 'HTML'], ['name' => 'JSON'], 
            ['name' => 'REST'], ['name' => 'GraphQL'], ['name' => 'Microservices']
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag['name'],
                'slug' => Str::slug($tag['name'])
            ]);
        }
    }
}