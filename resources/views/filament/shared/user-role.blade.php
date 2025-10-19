@php
    $user = auth()->user();
    $role = $user?->getRoleNames()->first();
@endphp

@if ($role)
    <div class="fi-dropdown-list-item-label text-sm font-medium text-gray-500 dark:text-gray-400 px-3 py-2">
        [ {{ ucfirst($role) }} ]
    </div>
@endif