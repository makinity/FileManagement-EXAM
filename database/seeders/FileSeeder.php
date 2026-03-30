<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        File::insert([
            [
                'file_name' => 'Student Handbook.pdf',
                'description' => 'Official student handbook for school policies and guidelines.',
                'file_path' => 'uploads/files/student-handbook.pdf',
                'file_type' => 'pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'file_name' => 'Enrollment Form.docx',
                'description' => 'Form used for new student enrollment.',
                'file_path' => 'uploads/files/enrollment-form.docx',
                'file_type' => 'docx',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'file_name' => 'Class Schedule.xlsx',
                'description' => 'Spreadsheet containing class schedules.',
                'file_path' => 'uploads/files/class-schedule.xlsx',
                'file_type' => 'xlsx',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'file_name' => 'School Logo.png',
                'description' => 'Official school logo image.',
                'file_path' => 'uploads/files/school-logo.png',
                'file_type' => 'png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'file_name' => 'Event Poster.jpg',
                'description' => 'Poster for the annual school event.',
                'file_path' => 'uploads/files/event-poster.jpg',
                'file_type' => 'jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
