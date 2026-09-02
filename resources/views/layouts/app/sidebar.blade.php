<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-950">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:bg-zinc-900 dark:border-zinc-800">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>

                <flux:sidebar.group :heading="__('Administration')" class="grid">
                    <flux:sidebar.item icon="layout-grid" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                        {{ __('Tableau de Bord') }}
                    </flux:sidebar.item>
                    @can('programs.view')
                        <flux:sidebar.item icon="map-pin" :href="route('admin.programs.index')" :current="request()->routeIs('admin.programs.*')" wire:navigate>
                            {{ __('Programmes') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('projects.view')
                        <flux:sidebar.item icon="building-office-2" :href="route('admin.projects.index')" :current="request()->routeIs('admin.projects.*')" wire:navigate>
                            {{ __('Projets') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('inquiries.view')
                        <flux:sidebar.item icon="envelope" :href="route('admin.inquiries.index')" :current="request()->routeIs('admin.inquiries.*')" wire:navigate>
                            {{ __('Prospects') }}
                        </flux:sidebar.item>
                    @endcan
                    @role('Super Admin')
                        <flux:sidebar.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                            {{ __('Utilisateurs') }}
                        </flux:sidebar.item>
                    @endrole
                    @canany(['cms.manage','seo.manage','theme.manage'])
                        <flux:sidebar.item icon="book-open-text" :href="route('admin.cms.index')" :current="request()->routeIs('admin.cms.*')" wire:navigate>
                            {{ __('CMS') }}
                        </flux:sidebar.item>
                    @endcanany
                    @can('testimonials.manage')
                        <flux:sidebar.item icon="chat-bubble-left-right" :href="route('admin.testimonials.index')" :current="request()->routeIs('admin.testimonials.*')" wire:navigate>
                            {{ __('Témoignages') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('partners.manage')
                        <flux:sidebar.item icon="building-storefront" :href="route('admin.partners.index')" :current="request()->routeIs('admin.partners.*')" wire:navigate>
                            {{ __('Partenaires') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('posts.view')
                        <flux:sidebar.item icon="newspaper" :href="route('admin.posts.index')" :current="request()->routeIs('admin.posts.*')" wire:navigate>
                            {{ __('Actualités') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('subscribers.view')
                        <flux:sidebar.item icon="envelope" :href="route('admin.subscribers.index')" :current="request()->routeIs('admin.subscribers.*')" wire:navigate>
                            {{ __('Newsletter') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('analytics.view')
                        <flux:sidebar.item icon="chart-bar" :href="route('admin.analytics.index')" :current="request()->routeIs('admin.analytics.*')" wire:navigate>
                            {{ __('Analytics') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
