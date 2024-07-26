<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestione Pagamenti') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="font-semibold text-lg mb-4">Abbonamenti</h3>
                <table class="min-w-full mb-8">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Utente</th>
                            <th class="px-4 py-2">Piano</th>
                            <th class="px-4 py-2">Stato</th>
                            <th class="px-4 py-2">Data Inizio</th>
                            <th class="px-4 py-2">Data Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $subscription)
                            <tr>
                                <td class="border px-4 py-2">{{ $subscription->id }}</td>
                                <td class="border px-4 py-2">{{ $subscription->user->name }}</td>
                                <td class="border px-4 py-2">{{ $subscription->name }}</td>
                                <td class="border px-4 py-2">{{ $subscription->stripe_status }}</td>
                                <td class="border px-4 py-2">{{ $subscription->created_at->format('d/m/Y H:i') }}</td>
                                <td class="border px-4 py-2">{{ $subscription->ends_at ? $subscription->ends_at->format('d/m/Y H:i') : 'Attivo' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $subscriptions->links() }}

                <h3 class="font-semibold text-lg mb-4 mt-8">Fatture</h3>
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Utente</th>
                            <th class="px-4 py-2">Totale</th>
                            <th class="px-4 py-2">Data</th>
                            <th class="px-4 py-2">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td class="border px-4 py-2">{{ $invoice['id'] }}</td>
                                <td class="border px-4 py-2">{{ $invoice['customer_name'] }}</td>
                                <td class="border px-4 py-2">{{ $invoice['total'] }}</td>
                                <td class="border px-4 py-2">{{ $invoice['date'] }}</td>
                                <td class="border px-4 py-2">
                                    <a href="{{ $invoice['download_url'] }}" class="text-blue-500 hover:text-blue-700">Scarica PDF</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Paginazione manuale -->
                @if($invoicesCount > 10)
                    <div class="mt-4">
                        @if($invoices->currentPage() > 1)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $invoices->currentPage() - 1]) }}">Precedente</a>
                        @endif

                        @if($invoices->currentPage() * 10 < $invoicesCount)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $invoices->currentPage() + 1]) }}">Successiva</a>
                        @endif
                    </div>
                @endif
                
            </div>
        </div>
    </div>
</x-app-layout>