<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Change Subscription Plan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                @if (session('error'))
                    <div class="mb-4 text-red-600">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('subscription.change') }}">
                    @csrf
                    <div class="space-y-4">
                        @foreach ($plans as $planKey => $plan)
                            <div class="flex items-center {{ $currentPlan === $plan['name'] ? 'opacity-50' : '' }}">
                                <input type="radio" id="{{ $planKey }}" name="plan" value="{{ $planKey }}" 
                                       {{ $currentPlan === $plan['name'] ? 'disabled' : '' }}
                                       class="form-radio h-4 w-4 text-indigo-600">
                                <label for="{{ $planKey }}" class="ml-2">
                                    {{ $plan['name'] }} - ${{ number_format($plan['price'] / 100, 2) }} / month
                                    @if ($currentPlan === $plan['name'])
                                        <span class="ml-2 text-sm text-green-600">(Current Plan)</span>
                                    @endif
                                </label>
                            </div>
                            <ul class="ml-6 list-disc">
                                @foreach ($plan['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        @if ($currentPlan === 'Standard')
                            <x-button type="submit" name="action" value="upgrade">
                                {{ __('Upgrade to Premium') }}
                            </x-button>
                        @else
                            <x-button type="submit" name="action" value="downgrade">
                                {{ __('Downgrade to Standard') }}
                            </x-button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>