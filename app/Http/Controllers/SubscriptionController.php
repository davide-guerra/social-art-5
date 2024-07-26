<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Price;

class SubscriptionController extends Controller
{
    public function create()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $standardPrice = Price::retrieve(env('STRIPE_STANDARD_PRICE_ID'));
        $premiumPrice = Price::retrieve(env('STRIPE_PREMIUM_PRICE_ID'));

        $plans = [
            'standard' => [
                'name' => 'Standard',
                'price_id' => env('STRIPE_STANDARD_PRICE_ID'),
                'price' => $standardPrice->unit_amount,
                'currency' => $standardPrice->currency,
                'features' => ['Feature 1', 'Feature 2'],
            ],
            'premium' => [
                'name' => 'Premium',
                'price_id' => env('STRIPE_PREMIUM_PRICE_ID'),
                'price' => $premiumPrice->unit_amount,
                'currency' => $premiumPrice->currency,
                'features' => ['Feature 1', 'Feature 2', 'Feature 3', 'Feature 4'],
            ],
        ];

        return view('subscription.create', [
            'intent' => auth()->user()->createSetupIntent(),
            'plans' => $plans,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $paymentMethod = $request->input('payment_method');
        $plan = $request->input('plan');

        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod($paymentMethod);

        $priceId = $plan === 'premium' ? env('STRIPE_PREMIUM_PRICE_ID') : env('STRIPE_STANDARD_PRICE_ID');

        $user->newSubscription('default', $priceId)
            ->create($paymentMethod);

        $user->status = 'active';
        $user->save();

        return redirect()->route('dashboard');

        // Ora è possibile accedere al piano dell'utente con:
        // $planName = $user->getPlanName();
        // In alternativa, all'interno di una vista blade:
        /* @if(auth()->check())
        Il tuo piano attuale è: {{ auth()->user()->getPlanName() }}
        @endif */

    }

    public function showChangePlan()
    {
        $user = auth()->user();
        $currentPlan = $user->getPlanName();

        $plans = [
            'standard' => [
                'name' => 'Standard',
                'price' => env('STRIPE_STANDARD_PRICE'),
                'features' => ['Feature 1', 'Feature 2'],
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => env('STRIPE_PREMIUM_PRICE'),
                'features' => ['Feature 1', 'Feature 2', 'Feature 3', 'Feature 4'],
            ],
        ];

        return view('subscription.change-plan', compact('currentPlan', 'plans'));
    }

    public function changePlan(Request $request)
    {
        $user = auth()->user();
        $newPlan = $request->input('plan');

        $currentPlanName = $user->getPlanName();
        
        if ($newPlan === strtolower($currentPlanName)) {
            return back()->with('error', 'You are already subscribed to this plan.');
        }

        $priceId = $newPlan === 'premium' ? env('STRIPE_PREMIUM_PRICE_ID') : env('STRIPE_STANDARD_PRICE_ID');

        try {
            if ($user->subscription('default')->hasIncompletePayment()) {
                return redirect()->route('cashier.payment', $user->subscription('default')->latestPayment()->id);
            }

            $user->subscription('default')->swap($priceId);

            $actionType = $newPlan === 'premium' ? 'upgraded' : 'downgraded';
            return redirect()->route('profile.show')->with('message', "Your subscription has been successfully {$actionType} to the {$newPlan} plan.");
        } catch (IncompletePayment $exception) {
            return redirect()->route('cashier.payment', [$exception->payment->id]);
        }
    }
}