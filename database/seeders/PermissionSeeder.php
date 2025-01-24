<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Role $role)
    {
        $modules = [
            'permissions',
            'roles',
            'users',
            'instructors',
            'learners',
            'subadmin',
            'settings',
            'categories',
            // 'email_templates',
            'dropdowns',
            'dropdown_options',
            'logs',
            'courses',
            'pages',
            'slider',
            'media',
            'news_letters',
            'blog',
            'faq',
            'wishlist',
            'packages',
            'course_review',
            'notifications',
            'contact',
            'support'
        ];

        foreach ($modules as $key => $value) {
            $permissions = ['restore_'.$value, 'delete_'.$value, 'add_'.$value, 'edit_'.$value, 'read_'.$value, 'browse_'.$value];
            foreach ($permissions as $key1 => $value1) {
                $permission = Permission::insertOrIgnore([
                    'name' => $value1,
                    'guard_name' => 'web',
                    'module_name' => $value,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        /* $permission_ids = Permission::where('module_name','users')->pluck('id')->toArray();
        $role->first()->syncPermissions($permission_ids); */
    }
}
