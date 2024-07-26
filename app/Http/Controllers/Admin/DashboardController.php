<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Cashier\Payment;
use Laravel\Cashier\Invoice;
use Laravel\Cashier\Subscription;
use App\Models\User;

class DashboardController extends Controller
{
    public function payments()
    {
        $subscriptions = Subscription::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $users = User::whereNotNull('stripe_id')->get();

        $invoices = collect();
        foreach ($users as $user) {
            $stripeInvoices = $user->invoicesIncludingPending();
            foreach ($stripeInvoices as $invoice) {
                $invoices->push([
                    'id' => $invoice->id,
                    'user_id' => $user->id,
                    'customer_name' => $user->name, // Usando il nome dell'utente invece di customer_name
                    'total' => $invoice->total(),
                    'date' => $invoice->date()->format('d/m/Y H:i'),
                    'download_url' => route('admin.invoice.download', $invoice->id)
                ]);
            }
        }

        $invoices = $invoices->sortByDesc('date')->values();
        $perPage = 10;
        $page = request()->get('page', 1);
        $pagedInvoices = $invoices->forPage($page, $perPage);

        return view('admin.payments', [
            'subscriptions' => $subscriptions,
            'invoices' => $pagedInvoices,
            'invoicesCount' => $invoices->count()
        ]);
    }

    public function downloadInvoice($invoiceId)
    {
        $user = auth()->user(); // Usa l'utente autenticato

        try {
            $invoice = $user->findInvoice($invoiceId);
            
            return $invoice->download([
                'vendor' => 'La tua azienda',
                'product' => 'Abbonamento',
            ]);
        } catch (\Exception $e) {
            // Log dell'errore
            \Log::error('Errore nel download della fattura: ' . $e->getMessage());
            
            // Reindirizza con un messaggio di errore
            return redirect()->back()->with('error', 'Impossibile scaricare la fattura. Contatta il supporto.');
        }
    }

}
