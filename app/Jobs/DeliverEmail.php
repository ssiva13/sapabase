<?php

namespace Acelle\Jobs;

class DeliverEmail extends SystemJob
{
    protected $email;
    protected $subscriber;

    /**
     * Create a new job instance.
     * @note: Parent constructors are not called implicitly if the child class defines a constructor.
     *        In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * @return void
     */
    public function __construct($email, $subscriber)
    {
        $this->email = $email;
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
        $this->email->deliverTo($this->subscriber);
    }
}
