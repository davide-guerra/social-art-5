<x-action-section>
    <x-slot name="title">
        {{ __('Subscription Status') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Manage your subscription status and view your billing history.') }}
    </x-slot>

    <x-slot name="content">
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($currentPlan)
            <div class="text-sm text-gray-600">
                {{ __('Current plan') }}: {{ $currentPlan }}
            </div>

            @if($nextPayment && $nextPaymentAmount !== null)
                <div class="text-sm text-gray-600 mt-2">
                    {{ __('Next payment') }}: {{ $nextPayment->format('F j, Y') }} - ${{ sprintf('%.2f', $nextPaymentAmount / 100) }}
                    @if ($prorationValue)
                        (proration: ${{ sprintf('%.2f', $prorationValue / 100) }})
                    @endif
                </div>
            @endif

            @if($cancellationDate)
                <div class="text-sm text-gray-600 mt-2">
                    {{ ('Your subscription will expire on') }}: {{ $cancellationDate->format('F j, Y') }}
                </div>
            @else
                <div class="mt-5 space-x-2">
                    <x-button wire:click="changePlan" wire:loading.attr="disabled">
                        {{ ('Change Plan') }}
                    </x-button>
                    <x-button wire:click="cancelSubscription" wire:loading.attr="disabled">
                        {{ ('Cancel Subscription') }}
                    </x-button>
                </div>
            @endif
        @else
            <div class="text-sm text-gray-600">
                {{ ('You don\'t have an active subscription.') }}
            </div>
            <div class="mt-5">
                <x-button href="{{ route('subscription.create') }}">
                    {{ __('Subscribe Now') }}
                </x-button>
            </div>
        @endif

        @if (count($invoices) > 0)
            <div class="mt-5">
                <h3 class="text-lg font-medium text-gray-900">{{ ('Recent Invoices') }}</h3>
                <div class="mt-4 space-y-2">
                    @foreach ($invoices as $invoice)
                        <div class="flex justify-between">
                            <div>{{ $invoice['date'] }}</div>
                            <div>{{ $invoice['total'] }}</div>
                            <div>
                                <a href="{{ $invoice['url'] }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                    {{ ('View') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-slot>
</x-action-section>