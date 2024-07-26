<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Scegli il tuo piano') }}
        </2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form action="{{ route('subscription.store') }}" method="POST" id="payment-form">
                    @csrf
                    <div class="mb-4">
                        @foreach($plans as $planKey => $plan)
                            <div class="mb-2">
                                <input type="radio" id="{{ $planKey }}" name="plan" value="{{ $planKey }}" required>
                                <label for="{{ $planKey }}">
                                    {{ $plan['name'] }} - {{ number_format($plan['price'] / 100, 2) }} {{ strtoupper($plan['currency']) }}
                                </label>
                                <ul>
                                    @foreach($plan['features'] as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-4">
                        <input id="card-holder-name" type="text" placeholder="Titolare della carta" required>
                    </div>

                    <div class="mb-4">
                        <div id="card-element"></div>
                    </div>

                    <button id="card-button" data-secret="{{ $intent->client_secret }}">
                        Sottoscrivi
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('{{ env('STRIPE_KEY') }}');
        const elements = stripe.elements();
        const cardElement = elements.create('card');

        cardElement.mount('#card-element');

        const cardHolderName = document.getElementById('card-holder-name');
        const cardButton = document.getElementById('card-button');
        const clientSecret = cardButton.dataset.secret;

        cardButton.addEventListener('click', async (e) => {
            e.preventDefault();
            const { setupIntent, error } = await stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: { name: cardHolderName.value }
                    }
                }
            );

            if (error) {
                console.error(error);
            } else {
                const form = document.getElementById('payment-form');
                const hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'payment_method');
                hiddenInput.setAttribute('value', setupIntent.payment_method);
                form.appendChild(hiddenInput);
                form.submit();
            }
        });
    </script>
    @endpush
</x-app-layout>