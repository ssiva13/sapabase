<?php

namespace Acelle\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Acelle\Model\User;
use Acelle\Model\Setting;
use Acelle\Cashier\Cashier;
use Acelle\Cashier\Subscription;

class SubscriptionPolicy
{
    use HandlesAuthorization;

    public function readAll(User $user, Subscription $subscription, $role)
    {
        switch ($role) {
            case 'admin':
                $can = $user->admin->getPermission('subscription_read') == 'all';
                break;
            case 'customer':
                $can = false;
                break;
        }

        return $can;
    }

    public function read(User $user, Subscription $subscription, $role)
    {
        switch ($role) {
            case 'admin':
                $can = $user->admin->getPermission('subscription_read') != 'no';
                break;
            case 'customer':
                $can = !$subscription->id || $user->customer->id == $subscription->customer_id;
                break;
        }

        return $can;
    }

    public function cancel(User $user, Subscription $subscription, $role)
    {
        $gateway = Cashier::getPaymentGateway();
        $can = $subscription->isActive() && $subscription->isRecurring() && $gateway->isSupportRecurring();
        switch ($role) {
            case 'admin':
                break;
            case 'customer':
                break;
        }

        return $can;
    }

    public function resume(User $user, Subscription $subscription, $role)
    {
        $gateway = Cashier::getPaymentGateway();
        $can = $gateway->isSupportRecurring() && $subscription->cancelled() && !$subscription->isEnded();

        switch ($role) {
            case 'admin':
                break;
            case 'customer':
                break;
        }

        return $can;
    }

    public function cancelNow(User $user, Subscription $subscription, $role)
    {
        $can = !$subscription->isEnded() && !$subscription->isNew();

        switch ($role) {
            case 'admin':
                break;
            case 'customer':
                break;
        }

        return $can;
    }

    public function invoices(User $user, Subscription $subscription, $role)
    {
        $can = isset($subscription);

        switch ($role) {
            case 'admin':
                break;
            case 'customer':
                break;
        }

        return $can;
    }

    public function changePlan(User $user, Subscription $subscription, $role)
    {
        $gateway = Cashier::getPaymentGateway();
        
        switch ($role) {
            case 'admin':
                $can = false;
                break;
            case 'customer':
                $can = $subscription->isActive() && !$gateway->hasPending($subscription);
                break;
        }

        return $can;
    }

    public function renew(User $user, Subscription $subscription, $role)
    {
        $gateway = Cashier::getPaymentGateway();
        $can = $subscription->isActive() && !$gateway->hasPending($subscription) && !$gateway->isSupportRecurring();
        $can = $can && (
            config('cashier.renew_free_plan') == 'yes' ||
            (config('cashier.renew_free_plan') == 'no' && !$subscription->plan->isFree())
        );

        $goingToExpire = $subscription->goingToExpire();

        // $can = $can && $goingToExpire;

        switch ($role) {
            case 'admin':
                break;
            case 'customer':
                break;
        }

        return $can;
    }

    ////
    public function create(User $user, Subscription $subscription, $role)
    {
        switch ($role) {
            case 'admin':
                $can = true;
                break;
            case 'customer':
                $can = !isset($subscription);
                break;
        }

        return $can;
    }

    public function subscribe(User $user, Subscription $subscription, $role)
    {
        switch ($role) {
            case 'admin':
                $can = false;
                break;
            case 'customer':
                $can = $subscription->isPending();
                break;
        }

        return $can;
    }

    public function checkout(User $user, Subscription $subscription, $role)
    {
        switch ($role) {
            case 'admin':
                $can = false;
                break;
            case 'customer':
                $can = $subscription->isNew() || $subscription->isPending();
                break;
        }

        return $can;
    }

    public function updateCard(User $user, Subscription $subscription, $role)
    {
        switch ($role) {
            case 'admin':
                $can = false;
                break;
            case 'customer':
                $can = $subscription->isPending();
                break;
        }

        return $can;
    }

    public function setActive(User $user, Subscription $subscription, $role)
    {
        $gateway = Cashier::getPaymentGateway();
        $can = $subscription->isPending();

        switch ($role) {
            case 'admin':
                break;
            case 'customer':
                $can = false;
                break;
        }

        return $can;
    }

    public function approvePending(User $user, Subscription $subscription, $role)
    {
        $gateway = Cashier::getPaymentGateway();
        $can = $gateway->hasPending($subscription);

        switch ($role) {
            case 'admin':
                break;
            case 'customer':
                $can = false;
                break;
        }

        return $can;
    }

    public function rejectPending(User $user, Subscription $subscription, $role)
    {
        $gateway = Cashier::getPaymentGateway();
        $can = $gateway->hasPending($subscription);

        switch ($role) {
            case 'admin':
                break;
            case 'customer':
                $can = false;
                break;
        }

        return $can;
    }

    public function delete(User $user, Subscription $subscription, $role)
    {
        $can = $subscription->isEnded() || $subscription->isNew();

        switch ($role) {
            case 'admin':
                break;
            case 'customer':
                break;
        }

        return $can;
    }
}
