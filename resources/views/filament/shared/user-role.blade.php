@php
    $user = auth()->user();
    $role = $user?->getRoleNames()->first();
    $facultyRank = $user?->facultyRank?->name;

    $allowedRoles = [
        'super admin',
        'super_admin',
        'admin',
        'validator',
    ];

    $roleIsAllowed = $role && in_array(strtolower($role), $allowedRoles);

    $canShow = $roleIsAllowed || $facultyRank;
@endphp

@if ($canShow)
    <div class="fi-dropdown-list-item-label flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400 px-3 py-2">
        @if ($roleIsAllowed)
            <x-filament::badge color="primary" size="sm">
                {{ ucfirst($role) }}
            </x-filament::badge>
        @endif

        @if ($facultyRank)
            <x-filament::badge color="primary" size="sm">
                {{ $facultyRank }}
            </x-filament::badge>
        @endif
    </div>
@endif