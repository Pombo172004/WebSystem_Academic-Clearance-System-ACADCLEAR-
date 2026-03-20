<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\College;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Colleges
        $colleges = [
            'College of Technology',
            'College of Arts and Sciences',
            'College of Business',
            'College of Education',
            'College of Public Administration and Governance'
        ];

        foreach ($colleges as $collegeName) {
            College::create(['name' => $collegeName]);
        }

        // Create School Admin
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@acadclear.com',
            'password' => Hash::make('password'),
            'role' => 'school_admin',
        ]);

        $this->command->info('Colleges and Admin created successfully!');
    }
}