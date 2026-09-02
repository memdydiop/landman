<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Projects BTP/Aménagement
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'projects.publish',
            // Programs / Lotissements
            'programs.view',
            'programs.create',
            'programs.update',
            'programs.delete',
            'programs.publish',
            // Plots / Lots
            'plots.view',
            'plots.create',
            'plots.update',
            'plots.delete',
            // Media
            'media.manage',
            // Inquiries / Prospects
            'inquiries.view',
            'inquiries.update',
            'inquiries.delete',
            'inquiries.export',
            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            // CMS
            'cms.manage',
            'seo.manage',
            'theme.manage',
            // Testimonials & Partners
            'testimonials.manage',
            'partners.manage',
            // Blog
            'posts.view',
            'posts.create',
            'posts.update',
            'posts.delete',
            // Newsletter
            'subscribers.view',
            'subscribers.export',
            'subscribers.delete',
            // Analytics
            'analytics.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $editeurBtp = Role::firstOrCreate(['name' => 'Editeur BTP']);
        $editeurBtp->givePermissionTo([
            'projects.view', 'projects.create', 'projects.update', 'projects.delete', 'projects.publish',
            'media.manage',
            'programs.view', 'plots.view',
            'inquiries.view',
            'analytics.view',
        ]);

        $commercial = Role::firstOrCreate(['name' => 'Commercial Lotissement']);
        $commercial->givePermissionTo([
            'programs.view', 'programs.create', 'programs.update', 'programs.delete', 'programs.publish',
            'plots.view', 'plots.create', 'plots.update', 'plots.delete',
            'inquiries.view', 'inquiries.update', 'inquiries.export',
            'projects.view',
            // CMS lecture pour autonomie commerciale (vitrine) — édition limitée SEO via cms.manage restreint
            'cms.manage', 'seo.manage',
            'analytics.view',
        ]);

        // Rôle dédié CMS — pour futur Commercial CMS light
        $cmsEditor = Role::firstOrCreate(['name' => 'Editeur CMS']);
        $cmsEditor->givePermissionTo([
            'cms.manage', 'seo.manage', 'theme.manage',
            'media.manage',
            'testimonials.manage', 'partners.manage',
            'projects.view', 'programs.view',
        ]);

        $editor = Role::firstOrCreate(['name' => 'Editeur']);
        $editor->givePermissionTo([
            'projects.view', 'projects.update',
            'programs.view',
            'inquiries.view',
            'analytics.view',
        ]);
    }
}
