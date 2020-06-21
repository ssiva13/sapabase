<?php /** @noinspection DuplicatedCode */

namespace Acelle\Library\Automation;

use Acelle\Model\Email;
use Acelle\Jobs\DeliverEmail;
use Acelle\Model\TwilioMessage;
use Acelle\Jobs\DeliverMessage;

class Send extends Action
{
    /**
     * send emails and messages
    */
    public function execute()
    {
        $subscriber = $this->autoTrigger->subscriber;
        if ($this->options['type'] == 'send-an-email') {

            if (!$subscriber->isActive()) {
                $this->autoTrigger->logger()->warning(sprintf('Subscriber "%s" is not active (current status: "%s")', $subscriber->email, $subscriber->status));

                return false;
            }

            if ($this->options['init'] == 'false' || $this->options['init'] == false) {
                $this->autoTrigger->logger()->warning('Email not set up yet');

                return false;
            }

            // IMPORTANT
            // If this is the latest also the last action of the workflow
            // no more execute, just return true
            if (!is_null($this->last_executed)) {
                $this->autoTrigger->logger()->warning('Send action already executed');

                return true; // in case of adding new element after this, but duplicate log
            }

            $email = $this->getEmail();

            if (config('app.demo') != true) {
                sleep(1); // to avoid same date/time with previous wait, wrong timeline order
                dispatch(new DeliverEmail($email, $this->autoTrigger->subscriber));
            }

            $this->autoTrigger->logger()->info(sprintf('Send email entitled "%s" to "%s", queued', $email->subject, $this->autoTrigger->subscriber->email));

            $this->recordLastExecutedTime();
            $this->evaluationResult = true;

            return true;

        }elseif ($this->options['type'] == 'twilio') {

            if (!$subscriber->isActive()) {
                $this->autoTrigger->logger()->warning(sprintf('Subscriber is not active (current status: "%s")', $subscriber->status));
                return false;
            }
            if ($this->options['init'] == 'false' || $this->options['init'] == false) {
                $this->autoTrigger->logger()->warning('Call/SMS not set up yet');

                return false;
            }

            // IMPORTANT
            // If this is the latest also the last action of the workflow
            // no more execute, just return true
            if (!is_null($this->last_executed)) {
                $this->autoTrigger->logger()->warning('Send action already executed');

                return true; // in case of adding new element after this, but duplicate log
            }
            $twiliomessage = $this->getNumber();

            if (config('app.demo') != true) {
                sleep(1); // to avoid same date/time with previous wait, wrong timeline order
                dispatch(new DeliverMessage($twiliomessage, $this->autoTrigger->subscriber));
            }

            $this->autoTrigger->logger()->info(sprintf('Send sms entitled "%s", queued', $twiliomessage->subject));

            $this->recordLastExecutedTime();
            $this->evaluationResult = true;

            return true;

        }
    }

    public function getEmail()
    {
        return Email::findByUid($this->options['email_uid']);
    }

    public function getNumber()
    {
        return TwilioMessage::findByUid($this->options['twilio_uid']);
    }

    public function getActionDescription()
    {
        $nameOrEmail = $this->autoTrigger->subscriber->getFullNameOrEmail();

        return sprintf('User %s receives email entitled "%s"', $nameOrEmail, $this->getEmail()->subject);
    }
}
