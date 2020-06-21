<?php


namespace Acelle\Jobs;


class DeliverMessage extends SystemJob
{
    protected $twiliomessage;
    protected $subscriber;

    /**
     * Create a new job instance.
     * @note: Parent constructors are not called implicitly if the child class defines a constructor.
     *        In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     * @param $twiliomessage
     * @param $subscriber
     */
    public function __construct($twiliomessage, $subscriber)
    {
        $this->twiliomessage = $twiliomessage;
        $this->subscriber = $subscriber;
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->twiliomessage->deliverTo($this->subscriber);
    }
}