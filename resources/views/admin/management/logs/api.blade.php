<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('API Integration Logs') }}</h2>
            <a href="{{ route('super-admin.dashboard') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Back to Dashboard') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold">
                            <th class="p-4">{{ __('Provider') }}</th>
                            <th class="p-4">{{ __('Action') }}</th>
                            <th class="p-4">{{ __('Status') }}</th>
                            <th class="p-4">{{ __('Reference ID') }}</th>
                            <th class="p-4">{{ __('Errors') }}</th>
                            <th class="p-4">{{ __('Timestamp') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-medium text-gray-900">{{ ucfirst($log->provider) }}</td>
                                <td class="p-4">{{ str_replace('_', ' ', $log->action) }}</td>
                                <td class="p-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $log->status === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="p-4 font-mono text-xs">{{ $log->reference ?? '-' }}</td>
                                <td class="p-4 text-xs text-red-600 font-medium max-w-xs truncate">{{ $log->error_message ?? '-' }}</td>
                                <td class="p-4 text-xs text-gray-400"><x-local-time :date="$log->created_at" format="seconds" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-500 italic">{{ __('No API integration logs found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($logs->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
