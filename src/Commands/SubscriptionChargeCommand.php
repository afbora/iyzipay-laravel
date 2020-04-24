<?php

namespace Afbora\IyzipayLaravel\Commands;

use Afbora\IyzipayLaravel\Events\SubscriptionCouldNotPaid;
use Afbora\IyzipayLaravel\Exceptions\Transaction\TransactionSaveException;
use Afbora\IyzipayLaravel\Models\Subscription;
use Afbora\IyzipayLaravel\PayableContract as Payable;
use Illuminate\Console\Command;

class SubscriptionChargeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iyzipay:subscription_charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charges subscription prices';

    public function handle()
    {
        $this->chargePayables($this->getWillBeChargedPayables());
    }

    private function getWillBeChargedPayables()
    {
        $subscriptions = Subscription::active()->get();

        $payables = collect();
        foreach ($subscriptions as $subscription) {
            $payables->push($subscription->owner);
        }

        return $payables;
    }

    private function chargePayables($payables)
    {
        $payables->each(function (Payable $payable) {
            try {
                $payable->paySubscription();
            } catch (TransactionSaveException $e) {
                event(new SubscriptionCouldNotPaid($payable));
            }
        });
    }
}
