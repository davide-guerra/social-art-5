<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionStatus extends Component
{
    public $user;
    public $currentPlan;
    public $invoices = [];
    public $nextPayment;
    public $nextPaymentAmount;
    public $cancellationDate;
    public $prorationValue;

    public function mount()
    {
        $this->user = auth()->user();
        $this->loadSubscriptionData();
    }

    public function loadSubscriptionData()
    {
        $this->user = auth()->user();
        $this->currentPlan = $this->user->getPlanName();
        
        $this->invoices = $this->user->invoices()->take(10)->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'date' => $invoice->date()->toFormattedDateString(),
                'total' => $invoice->total(),
                'url' => $invoice->hosted_invoice_url,
            ];
        })->toArray();
        
        if ($this->user->subscribed('default')) {
            $subscription = $this->user->subscription('default');
            $this->nextPayment = Carbon::createFromTimestamp($subscription->asStripeSubscription()->current_period_end);

            try {
                $upcomingInvoice = $this->user->upcomingInvoice();
                // Utilizziamo rawTotal() invece di total()
                $this->nextPaymentAmount = $upcomingInvoice->rawTotal();

                // Cerchiamo un'eventuale proration
                $prorationAmount = collect($upcomingInvoice->invoiceItems())->reduce(function ($carry, $item) {
                    if (strpos($item->description, 'Remaining time') !== false || 
                        strpos($item->description, 'Unused time') !== false) {
                        return $carry + $item->amount;
                    }
                    return $carry;
                }, 0);
                
                $this->prorationValue = $prorationAmount;
                
            } catch (IncompletePayment $exception) {
                $this->nextPaymentAmount = null;
            } catch (\Exception $e) {
                $this->nextPaymentAmount = null;
            }

            if ($subscription->canceled() && $subscription->onGracePeriod()) {
                $this->cancellationDate = $subscription->ends_at;
            } else {
                $this->cancellationDate = null;
            }
        } else {
            $this->nextPayment = null;
            $this->nextPaymentAmount = null;
            $this->cancellationDate = null;
        }
    }

    public function changePlan()
    {
        return redirect()->route('subscription.change');
    }

    public function cancelSubscription()
    {
        $subscription = $this->user->subscription('default');
        if ($subscription && !$subscription->canceled()) {
            $subscription->cancel();
            $this->loadSubscriptionData();
            session()->flash('message', 'Your subscription has been cancelled.');
        } else {
            session()->flash('error', 'Unable to cancel subscription.');
        }
    }

    public function render()
    {
        return view('livewire.subscription-status');
    }
}