<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Pemohon permissions
        $pemohonPermissions = [
            'project.create',
            'project.read.own',
            'project.update.own.draft',
            'project.submit',
            'project.resubmit',
            'document.upload',
            'document.download.own',
            'history.read.own',
            'dashboard.pemohon',
        ];

        // Penilai permissions
        $penilaiPermissions = [
            'project.read.all',
            'project.review',
            'project.approve',
            'project.revise',
            'project.reject',
            'review.create',
            'history.read.all',
            'document.download.all',
            'dashboard.penilai',
        ];

        $allPermissions = array_unique(array_merge($pemohonPermissions, $penilaiPermissions));

        // Create permissions
        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign permissions
        $rolePemohon = Role::findOrCreate('pemohon');
        $rolePemohon->syncPermissions(Permission::whereIn('name', $pemohonPermissions)->get());

        $rolePenilai = Role::findOrCreate('penilai');
        $rolePenilai->syncPermissions(Permission::whereIn('name', $penilaiPermissions)->get());
    }
}
