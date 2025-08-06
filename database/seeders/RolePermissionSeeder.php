<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            // Usuarios
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Empresas
            'view companies',
            'create companies',
            'edit companies',
            'delete companies',
            'manage company membership',
            
            // Posts
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'moderate posts',
            
            // Eventos
            'view events',
            'create events',
            'edit events',
            'delete events',
            'manage event attendance',
            
            // Conexiones
            'view connections',
            'manage connections',
            
            // Administración
            'access admin panel',
            'manage memberships',
            'view analytics',
            'manage system settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles
        $adminRole = Role::create(['name' => 'admin']);
        $moderatorRole = Role::create(['name' => 'moderator']);
        $memberRole = Role::create(['name' => 'member']);
        $guestRole = Role::create(['name' => 'guest']);

        // Asignar permisos a roles
        $adminRole->givePermissionTo(Permission::all());

        $moderatorRole->givePermissionTo([
            'view users',
            'view companies',
            'view posts',
            'moderate posts',
            'view events',
            'manage event attendance',
            'view connections',
            'view analytics',
        ]);

        $memberRole->givePermissionTo([
            'view users',
            'view companies',
            'view posts',
            'create posts',
            'edit posts',
            'view events',
            'create events',
            'edit events',
            'view connections',
            'manage connections',
        ]);

        $guestRole->givePermissionTo([
            'view users',
            'view companies',
            'view posts',
            'view events',
        ]);

        // Asignar roles a usuarios existentes
        $users = User::all();
        
        if ($users->count() > 0) {
            // Primer usuario como admin
            $users->first()->assignRole('admin');
            
            // Resto como members
            $users->skip(1)->each(function ($user) {
                $user->assignRole('member');
            });
        }
    }
}