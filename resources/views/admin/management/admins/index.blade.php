<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Admin Accounts Management') }}</h2>
            <a href="{{ route('super-admin.admins.create') }}" class="rounded bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700">
                {{ __('Create Admin') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold">
                            <th class="p-4">{{ __('Name') }}</th>
                            <th class="p-4">{{ __('Email') }}</th>
                            <th class="p-4">{{ __('Status') }}</th>
                            <th class="p-4">{{ __('Created At') }}</th>
                            <th class="p-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($admins as $admin)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-medium text-gray-900">{{ $admin->name }}</td>
                                <td class="p-4 text-gray-500">{{ $admin->email }}</td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $admin->status === 'active' ? 'bg-green-50 text-green-700' : ($admin->status === 'suspended' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ ucfirst($admin->status) }}
                                    </span>
                                </td>
                                <td class="p-4 text-gray-400"><x-local-time :date="$admin->created_at" /></td>
                                <td class="p-4 text-right space-x-2">
                                    <a href="{{ route('super-admin.admins.edit', $admin) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">{{ __('Edit') }}</a>
                                    
                                    <form method="POST" action="{{ route('super-admin.admins.suspend', $admin) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-amber-600 hover:text-amber-900 font-semibold">
                                            {{ $admin->status === 'suspended' ? __('Activate') : __('Suspend') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('super-admin.admins.destroy', $admin) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this Admin?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-500 italic">{{ __('No Admin accounts found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($admins->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $admins->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
