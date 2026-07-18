<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Delivery Zones') }}</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        @if(session('status'))<div class="rounded bg-green-50 p-4 text-green-700">{{ session('status') }}</div>@endif
        <form method="POST" action="{{ route('super-admin.delivery.zones.store') }}" class="grid gap-3 rounded-lg bg-white p-5 shadow sm:grid-cols-4">@csrf
            <x-text-input name="name" placeholder="Zone name" required /><x-text-input name="district" placeholder="District" required /><x-text-input name="province" placeholder="Province" /><x-text-input name="radius_km" type="number" min="0" step="0.01" placeholder="Radius (KM)" />
            <x-text-input name="latitude" type="number" step="0.0000001" placeholder="Latitude" /><x-text-input name="longitude" type="number" step="0.0000001" placeholder="Longitude" /><x-text-input name="estimated_delivery_minutes" type="number" min="1" placeholder="ETA minutes" />
            <select name="status" class="rounded border-gray-300"><option value="active">Active</option><option value="inactive">Inactive</option></select><x-primary-button>{{ __('Add zone') }}</x-primary-button>
        </form>
        <div class="overflow-x-auto rounded-lg bg-white shadow"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left"><tr><th class="p-3">Zone</th><th>District / Province</th><th>Radius</th><th>ETA</th><th>Status</th></tr></thead><tbody class="divide-y">@forelse($zones as $zone)<tr><td class="p-3 font-semibold">{{ $zone->name }}</td><td>{{ $zone->district }}{{ $zone->province ? ', '.$zone->province : '' }}</td><td>{{ $zone->radius_km ?? '—' }} km</td><td>{{ $zone->estimated_delivery_minutes ? $zone->estimated_delivery_minutes.' min' : '—' }}</td><td>{{ ucfirst($zone->status) }}</td></tr>@empty<tr><td colspan="5" class="p-6 text-center text-gray-500">No delivery zones configured.</td></tr>@endforelse</tbody></table><div class="p-4">{{ $zones->links() }}</div></div>
    </div></div>
</x-app-layout>
