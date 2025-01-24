<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Role $role)
    {
        
        Role::insertOrIgnore([
            'name' => 'admin',
            'guard_name' => 'web',
            'display_name' => 'Admin',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        User::insertOrIgnore([
            'name' => 'Admin',
            'email' => 'lifegurukul@yopmail.com',
            'password' => Hash::make('123456789'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $user = User::firstOrFail();
        if($user && !$user->hasRole('admin')){
            $user->assignRole('admin');
        }
        // $roles_ids = Permission::get()->pluck('id')->toArray();
        // $role->first()->syncPermissions($roles_ids);
        // dd($roles_ids);


        Role::insertOrIgnore([
            'name' => 'subadmin',
            'guard_name' => 'web',
            'display_name' => 'Sub Admin',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        Role::insertOrIgnore([
            'name' => 'instructor',
            'guard_name' => 'web',
            'display_name' => 'Instructor',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $admin_permission_ids = Permission::get()->pluck('id')->toArray();
        // dd($admin_permission_ids);
        $role->first()->syncPermissions($admin_permission_ids);
        
        // Subadmin
        $subadminArray=[
            'subadmin'
        ];
        $subadmin_permission_ids = Permission::whereIn('module_name',$subadminArray)->pluck('id')->toArray();
        $role->where('name', 'subadmin')->first()->syncPermissions($subadmin_permission_ids);

        // Instructure
        $instructorArray=[
            'courses',
            'packages'
        ];
        $instructors_permission_ids = Permission::whereIn('module_name',$instructorArray)->pluck('id')->toArray();
        $role->where('name', 'instructor')->first()->syncPermissions($instructors_permission_ids);

    }
}
