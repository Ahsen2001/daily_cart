<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">{{ __('Role & Permission Management') }}</h2></x-slot>
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))<div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>@endif
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-600">{{ __('Roles remain separate. Use this screen to review and assign Spatie permissions per role without merging user roles.') }}</p>
            </div>
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($roles as $role)
                    <form method="POST" action="{{ route('super-admin.roles.update', $role) }}" class="rounded-lg bg-white p-6 shadow-sm">
                        @csrf
                        @method('PUT')
                        <h3 class="text-lg font-bold">{{ $role->name }}</h3>
                        <div class="mt-4 max-h-72 space-y-2 overflow-auto rounded-lg border border-gray-100 p-3">
                            @forelse ($permissions as $permission)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked($role->hasPermissionTo($permission->name))>
                                    <span>{{ $permission->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('No permissions have been created yet.') }}</p>
                            @endforelse
                        </div>
                        <x-primary-button class="mt-4">{{ __('Save Permissions') }}</x-primary-button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
