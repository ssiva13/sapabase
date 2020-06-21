<?php

/**
 * Automation class.
 *
 * Model for automations
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

use DB;
use Acelle\Library\Automation\Action;
use Acelle\Library\Automation\Trigger;
use Acelle\Library\Automation\Send;
use Acelle\Library\Automation\Wait;
use Acelle\Library\Automation\Evaluate;
use Acelle\Library\Automation\Operate;
use Carbon\Carbon;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Automation2 extends Model
{
    // Automation status
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Items per page.
     *
     * @var array
     */
    const ITEMS_PER_PAGE = 25;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Association with mailList through mail_list_id column.
     */
    public function mailList()
    {
        return $this->belongsTo('Acelle\Model\MailList');
    }

    /**
     * Get all of the links for the automation.
     */
    public function emailLinks()
    {
        return $this->hasManyThrough('Acelle\Model\EmailLink', 'Acelle\Model\Email');
    }

    /**
     * Get all of the emails for the automation.
     */
    public function emails()
    {
        return $this->hasMany('Acelle\Model\Email');
    }

    /**
     * Association.
     */
    public function autoTriggers()
    {
        return $this->hasMany('Acelle\Model\AutoTrigger');
    }

    /**
     * Association.
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    /**
     * Association.
     */
    public function timelines()
    {
        return $this->hasMany('Acelle\Model\Timeline')->orderBy('created_at', 'DESC');
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating automation.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Create automation rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'mail_list_uid' => 'required',
        ];
    }

    public function getElementById($id)
    {
        // search by id
        return $this->getElements()[$id];
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::where('customer_id', '=', $user->customer->id);

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%'.$request->keyword.'%');
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function search($request)
    {
        $query = self::filter($request);

        $query = $query->orderBy($request->sort_order, $request->sort_direction);

        return $query;
    }

    /**
     * enable automation.
     */
    public function enable()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * disable automation.
     */
    public function disable()
    {
        $this->status = self::STATUS_INACTIVE;
        $this->save();
    }

    /**
     * disable automation.
     */
    public function saveData($data)
    {
        $this->data = $data;
        $this->save();
    }

    /**
     * disable automation.
     */
    public function getData()
    {
        return isset($this->data) ? preg_replace('/"([^"]+)"\s*:\s*/', '$1:', $this->data) : '[]';
    }

    /**
     * get all tree elements.
     */
    public function getElements($hash = false) # true => return hash, false (default) => return stdObject
    {
        return isset($this->data) && !empty($this->data) ? json_decode($this->data, $hash) : [];
    }

    /**
     * get trigger.
     */
    public function getTrigger()
    {
        $elements = $this->getElements();

        return empty($elements) ? new AutomationElement(null) : new AutomationElement($elements[0]);
    }

    /**
     * get element by id.
     */
    public function getElement($id = null)
    {
        $elements = $this->getElements();

        foreach ($elements as $element) {
            if ($element->id == $id) {
                return new AutomationElement($element);
            }
        }

        return new AutomationElement(null);
    }

    /**
     * Get started time.
     */
    public function getStartedTime()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get delay options.
     */
    public function getDelayOptions()
    {
        return [
            ['text' => trans_choice('messages.automation.delay.minute', 1), 'value' => '1 minute'],
            ['text' => trans_choice('messages.automation.delay.minute', 30), 'value' => '30 minutes'],
            ['text' => trans_choice('messages.automation.delay.hour', 1), 'value' => '1 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 2), 'value' => '2 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 4), 'value' => '4 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 8), 'value' => '8 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 12), 'value' => '12 hours'],

            ['text' => trans_choice('messages.automation.delay.day', 1), 'value' => '1 day'],
            ['text' => trans_choice('messages.automation.delay.day', 2), 'value' => '2 days'],
            ['text' => trans_choice('messages.automation.delay.day', 3), 'value' => '3 days'],
            ['text' => trans_choice('messages.automation.delay.day', 4), 'value' => '4 days'],
            ['text' => trans_choice('messages.automation.delay.day', 5), 'value' => '5 days'],
            ['text' => trans_choice('messages.automation.delay.day', 6), 'value' => '6 days'],
            ['text' => trans_choice('messages.automation.delay.week', 1), 'value' => '1 week'],
            ['text' => trans_choice('messages.automation.delay.week', 2), 'value' => '2 weeks'],
            ['text' => trans_choice('messages.automation.delay.month', 1), 'value' => '1 month'],
            ['text' => trans_choice('messages.automation.delay.month', 2), 'value' => '2 months'],
        ];
    }

    /**
     * Get delay or before options.
     */
    public function getDelayBeforeOptions()
    {
        return [
            ['text' => trans_choice('messages.automation.delay.day', 1), 'value' => '1 day'],
            ['text' => trans_choice('messages.automation.delay.day', 2), 'value' => '2 days'],
            ['text' => trans_choice('messages.automation.delay.day', 3), 'value' => '3 days'],
            ['text' => trans_choice('messages.automation.delay.day', 4), 'value' => '4 days'],
            ['text' => trans_choice('messages.automation.delay.day', 5), 'value' => '5 days'],
            ['text' => trans_choice('messages.automation.delay.day', 6), 'value' => '6 days'],
            ['text' => trans_choice('messages.automation.delay.week', 1), 'value' => '1 week'],
            ['text' => trans_choice('messages.automation.delay.week', 2), 'value' => '2 weeks'],
            ['text' => trans_choice('messages.automation.delay.month', 1), 'value' => '1 month'],
            ['text' => trans_choice('messages.automation.delay.month', 2), 'value' => '2 months'],
        ];
    }

    /**
     * Get email links options.
     */
    public function getEmailLinkOptions()
    {
        $data = [];

        foreach ($this->emailLinks as $link) {
            $data[] = ['text' => $link->link, 'value' => $link->uid];
        }

        return $data;
    }

    /**
     * Get emails options.
     */
    public function getEmailOptions()
    {
        $data = [];

        foreach ($this->emails as $email) {
            $data[] = ['text' => $email->subject, 'value' => $email->uid];
        }

        return $data;
    }

    /**
     * Initiate a trigger with a given subscriber.
     */
    public function initTrigger($subscriber)
    {
        $trigger = null;
        DB::transaction(function () use (&$trigger, $subscriber) {
            $trigger = $this->autoTriggers()->create();
            $trigger->subscriber()->associate($subscriber); // assign to subscriber_id field
            $trigger->updateWorkflow();
            $trigger->save();
        });

        return $trigger;
    }

    /**
     * Scan for triggers updates and new triggers.
     */
    public static function run()
    {
        $customers = Customer::all();
        foreach ($customers as $customer) {
            $automations = $customer->activeAutomation2s;
            foreach ($automations as $automation) {
                if ($customer->isSubscriptionActive()) {
                    $automation->logger()->info(sprintf('Checking automation "%s"', $automation->name));
                    $automation->checkForNewTriggers();
                    $automation->checkForExistingTriggersUpdate();
                } else {
                    $automation->logger()->warning(sprintf('Automation "%s" skipped, user "%s" not on active subscription', $automation->name, $customer->displayName()));
                }
            }
        }
    }

    /**
     * Check for new triggers.
     */
    public function checkForNewTriggers()
    {
        $this->logger()->info(sprintf('NEW > Start checking for new trigger'));

        switch ($this->getTriggerAction()->getOption('key')) {
            case 'welcome-new-subscriber':
                $this->checkForListSubscription();
                break;
            case 'specific-date':
                $this->checkForSpecificDatetime();
                //...
                break;
            case 'say-goodbye-subscriber':
                $this->checkForListUnsubscription();
                //...
                break;
            case 'api-3-0':
                // Just wait for API call
                break;
            case 'others':
                // others
                break;
            default:
                throw new \Exception('Unknown Automation trigger type '.$this->getTriggerAction()->getOption('key'));
        }
        $this->logger()->info(sprintf('NEW > Finish checking for new trigger'));
    }

    /**
     * Check for existing triggers update.
     */
    public function checkForExistingTriggersUpdate()
    {
        $this->logger()->info(sprintf('UPDATE > Start checking for trigger update'));
        foreach ($this->autoTriggers as $trigger) {
            $trigger->check();
        }
        $this->logger()->info(sprintf('UPDATE > Finish checking for trigger update'));
    }

    /**
     * Check for list-subscription events.
     */
    public function checkForListSubscription()
    {
        $this->logger()->info(sprintf('NEW > Check for List Subscription'));
        $now = Carbon::now();
        $subscribers = $this->getNewSubscribersToFollow();
        $total = count($subscribers);
        $this->logger()->info(sprintf('NEW > There are %s new subscriber(s) found', $total));

        $i = 0;
        foreach ($subscribers as $subscriber) {
            $i += 1;
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > (%s/%s) > Adding new trigger for %s', $i, $total, $subscriber->email));
        }
    }

    /**
     * Check for specific-datetimetime events.
     */
    public function checkForSpecificDatetime()
    {
        $this->logger()->info(sprintf('NEW > Check for Specific Date/Time'));
        // this is a one-time triggered automation event
        // just abort if it is already triggered
        if ($this->autoTriggers()->exists()) {
            $this->logger()->info(sprintf('NEW > Already triggered'));

            return;
        }

        $now = Carbon::now();
        $trigger = $this->getTriggerAction();

        $eventDate = Carbon::parse($trigger->getOption('date').' '.$trigger->getOption('at'));
        $checked = $now->gte($eventDate);

        $total = $this->subscribers()->count();
        $i = 0;
        if ($checked) {
            $this->logger()->info(sprintf('NEW > It is %s hours due! triggering!', $now->diffInHours($eventDate)));
            foreach ($this->subscribers()->get() as $subscriber) {
                $i += 1;
                $this->initTrigger($subscriber);
                $this->logger()->info(sprintf('NEW > (%s/%s) > Adding new trigger for %s', $i, $total, $subscriber->email));
            }
        }
    }

    /**
     * Check for follow-up-clicked events.
     */
    public function checkForListUnsubscription()
    {
        $this->logger()->info(sprintf('NEW > Check for List Unsubscription'));
        $subscribers = $this->getUnsubscribersToFollow();
        $total = count($subscribers);
        $this->logger()->info(sprintf('NEW > %s new unsubscribers found', $total));
        $i = 0;
        foreach ($subscribers as $subscriber) {
            $i += 1;
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > (%s/%s) > Adding new trigger for %s', $i, $total, $subscriber->email));
        }
    }

    /**
     * Get previous event's opened messages to follow up.
     *
     * @return collection
     */
    public function getNewSubscribersToFollow()
    {
        // Boot performance with temporary table + index
        try {
            \DB::statement('DROP TEMPORARY TABLE `_new_subscribers_to_follow`');
        } catch (\Exception $ex) {
            // just ignore, in case 2 queries in the same MySQL connection session
        }

        \DB::statement(sprintf('
            CREATE TEMPORARY TABLE `_new_subscribers_to_follow` AS
            SELECT COALESCE(subscriber_id, 0) AS subscriber_id
            FROM %s WHERE automation2_id = %s;

            CREATE INDEX _new_subscribers_to_follow_index ON _new_subscribers_to_follow(subscriber_id);', table('auto_triggers'), $this->id)
        );

        return $this->subscribers()->whereRaw(table('subscribers.id').' NOT IN (SELECT subscriber_id FROM _new_subscribers_to_follow)')
                ->where('subscribers.created_at', '>=', $this->created_at)
                ->where('mail_list_id', $this->mailList->id)
                ->whereRaw(sprintf('COALESCE('.table('subscribers.subscription_type').", '') <> %s", db_quote(Subscriber::SUBSCRIPTION_TYPE_IMPORTED)))
                ->get();
    }

    /**
     * Get previous event's unsubscribed messages to follow up.
     *
     * @return collection
     */
    public function getUnsubscribersToFollow()
    {
        return $this->subscribers()
                    ->select('subscribers.*')
                    ->join('unsubscribe_logs', 'subscribers.id', '=', 'unsubscribe_logs.subscriber_id')
                    ->leftJoin('auto_triggers', 'subscribers.id', '=', 'auto_triggers.subscriber_id')
                    ->whereNull('auto_triggers.id')
                    ->where('unsubscribe_logs.created_at', '>=', $this->created_at)
                    ->get();
    }

    public function logger()
    {
        $pid = getmypid();
        $formatter = new LineFormatter("[%datetime%] #{$pid} %channel%.%level_name%: %message%\n");
        if (getenv('LOGFILE') != false) {
            $stream = new RotatingFileHandler(getenv('LOGFILE'), 0, Logger::INFO);
        } else {
            $stream = new RotatingFileHandler(storage_path('logs/automation.log'), 0, Logger::INFO);
        }

        $stream->setFormatter($formatter);

        $logger = new Logger($this->uid);
        $logger->pushHandler($stream);

        return $logger;
    }

    public function timelinesBy($subscriber)
    {
        $trigger = $this->autoTriggers()->where('subscriber_id', $subscriber->id)->first();

        return (is_null($trigger)) ? Timeline::whereRaw('1=2') : $trigger->timelines(); // trick - return an empty Timeline[] array
    }

    public function getInsight()
    {
        if (!$this->data) {
            return [];
        }

        $actions = json_decode($this->data, true);
        $insights = [];
        foreach ($actions as $action) {
            $insights[$action['id']] = $this->getActionStats($action);
        }

        return $insights;
    }

    public function getActionStats($attributes)
    {
        $total = $this->mailList->readCache('SubscriberCount');

        // The following implementation is prettier but 'countBy' is supported in Laravel 5.8 only
        // $count = $this->autoTriggers()->countBy(function($trigger) {
        //    return $trigger->isActionExecuted($action['id']);
        // });
        $action = $this->getAction($attributes); // IMPORTANT: this action does not associate with a particular trigger
        $count = 0;
        foreach ($this->autoTriggers as $trigger) {
            $count += ($trigger->isActionExecuted($action->getId())) ? 1 : 0;
        }

        if ($action->getType() == 'ElementTrigger') {
            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.triggered', ['count' => $count]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementOperation') {
            $count = 0;
            foreach ($this->autoTriggers as $trigger) {
                $count += ($trigger->isLatest($action->getId())) ? 1 : 0;
            }

            $insight = [
                'count' => $count,
                'subtitle' => '#Operation TBD',
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementWait') {
            $count = 0;
            foreach ($this->autoTriggers as $trigger) {
                $count += ($trigger->isLatest($action->getId())) ? 1 : 0;
            }

            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.in-queue', ['count' => $count]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementAction') {
            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.sent', ['count' => $count]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementCondition') {
            $yes = 0;
            $no = 0;
            foreach ($this->autoTriggers as $trigger) {
                // instantiate the object again, this time with associated autotrigger
                $actionWithAutoTrigger = $trigger->getActionById($action->getId());

                if (is_null($actionWithAutoTrigger->getLastExecuted())) {
                    continue;
                }

                if ($actionWithAutoTrigger->getEvaluationResult()) {
                    $yes += 1;
                } else {
                    $no += 1;
                }
            }

            $insight = [
                'count' => $yes + $no,
                'subtitle' => __('messages.automation.stats.condition', ['yes' => $yes, 'no' => $no]),
                'percentage' => ($total != 0) ? (($yes + $no) / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        }

        return $insight;
    }

    public function getSummaryStats()
    {
        $total = $this->mailList->readCache('SubscriberCount');
        $involved = $this->autoTriggers()->count();

        $complete = 0;
        foreach ($this->autoTriggers as $trigger) {
            $complete += ($trigger->isComplete()) ? 1 : 0;
        }

        $completePercentage = ($total == 0) ? 0 : $complete / $total;

        return [
            'total' => $total,
            'involed' => $involved,
            'complete' => $completePercentage,
        ];
    }

    // for debugging only
    public function getTriggerAction() : Trigger
    {
        $trigger = null;
        $this->getActions(function ($e) use (&$trigger) {
            if ($e->getType() == 'ElementTrigger') {
                $trigger = $e;
            }
        });

        return  $trigger;
    }

    // by Louis
    public function getActions($callback)
    {
        $actions = $this->getElements(true);

        foreach ($actions as $action) {
            $instance = $this->getAction($action);
            $callback($instance);
        }
    }

    // IMPORTANT: object returned by this function is not associated with a particular AutoTrigger
    public function getAction($attributes) : Action
    {
        switch ($attributes['type']) {
            case 'ElementTrigger':
                $instance = new Trigger($attributes);
                break;
            case 'ElementAction':
                $instance = new Send($attributes);
                break;
            case 'ElementCondition':
                $instance = new Evaluate($attributes);
                break;
            case 'ElementWait':
                $instance = new Wait($attributes);
                break;
            case 'ElementOperation':
                $instance = new Operate($attributes);
                break;
            default:
                throw new \Exception('Unknown Action type '.$attributes['type']);
        }

        return $instance;
    }

    public function subscribers($actionId = null)
    {
        $list = $this->mailList->subscribers();

        if (!is_null($actionId)) {
            $list->join('auto_triggers', 'auto_triggers.subscriber_id', '=', 'subscribers.id')
                 ->where('auto_triggers.executed_index', 'LIKE', '%'.$actionId.'%');
        }

        return $list;
    }

    public function getIntro()
    {
        $triggerType = $this->getTriggerAction()->getOption('key');
        $translationKey = 'messages.automation.intro.'.$triggerType;

        return __($translationKey, ['list' => $this->mailList->name]);
    }

    public function getBriefIntro()
    {
        $triggerType = $this->getTriggerAction()->getOption('key');
        $translationKey = 'messages.automation.brief-intro.'.$triggerType;

        return __($translationKey, ['list' => $this->mailList->name]);
    }

    public function countEmails()
    {
        $count = 0;
        $this->getActions(function ($e) use (&$count) {
            if (($e->getType() == 'ElementAction') && ($e->getOption('type') == 'send-an-email')) {
                $count += 1;
            }
        });
        return $count;
    }

    public function countNumbers()
    {
        $count = 0;
        $this->getActions(function ($e) use (&$count) {
            if (($e->getType() == 'ElementAction') && ($e->getOption('type') == 'twilio')) {
                $count += 1;
            }
        });
        return $count;
    }

    /**
     * Get recent automations for switch.
     */
    public function getSwitchAutomations($customer)
    {
        return $customer->automation2s()->where('id', '<>', $this->id)->orderBy('updated_at', 'desc')->limit(50);
    }

    /**
     * Get list fields options.
     */
    public function getListFieldOptions()
    {
        $data = [];

        foreach ($this->mailList->getFields()->get() as $field) {
            $data[] = ['text' => $field->label, 'value' => $field->uid];
        }

        return $data;
    }

    /**
     * Produce sample data.
     */
    public function produceSampleData()
    {
        // Reset all
        $this->resetListRelatedData();

        $count = $this->mailList->readCache('SubscriberCount');

        $min = (int) ($count * 0.2);
        $max = (int) ($count * 0.7);

        // Generate triggers
        $subscribers = $this->subscribers()->inRandomOrder()->limit(rand($min, $max))->get();
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
        }

        // Run through trigger check
        $this->checkForExistingTriggersUpdate();
    }

    /**
     * Clean up after list change.
     */
    public function resetListRelatedData()
    {
        // Delete autoTriggers will also delete
        // + tracking_logs
        // + open logs
        // + click logs
        // + timelines
        $this->autoTriggers()->delete();
    }

    /**
     * Change mail list.
     */
    public function updateMailList($new_list)
    {
        if ($this->mail_list_id != $new_list->id) {
            $this->mail_list_id = $new_list->id;
            $this->save();

            // reset automation list
            $this->resetListRelatedData();
        }
    }

    /**
     * Fill from request.
     */
    public function fillRequest($request)
    {
        // fill attributes
        $this->fill($request->all());

        // fill segments
        $segments = [];
        $this->segment_id = null;
        if (!empty($request->segment_uid)) {
            foreach ($request->segment_uid as $segmentUid) {
                $segments[] = \Acelle\Model\Segment::findByUid($segmentUid)->id;
            }

            if (!empty($segments)) {
                $this->segment_id = implode(',', $segments);
            }
        }
    }

    /**
     * Get segments.
     */
    public function getSegments()
    {
        if (!$this->segment_id) {
            return collect([]);
        }

        $segments = \Acelle\Model\Segment::whereIn('id', explode(',', $this->segment_id))->get();

        return $segments;
    }

    /**
     * Get segments uids.
     */
    public function getSegmentUids()
    {
        return $this->getSegments()->map->uid->toArray();
    }

    // for debugging only
    public function updateActionOptions($actionId, $data = [])
    {
        $json = json_decode($this->data, true);

        for ($i = 0; $i < sizeof($json); $i += 1) {
            $action = $json[$i];
            if ($action['id'] != $actionId) {
                continue;
            }

            $action['options'] = array_merge($action['options'], $data);

            $json[$i] = $action;
            $this->data = json_encode($json);
            $this->save();
        }
    }

    /**
     * Get segments uids.
     */
    public function execute()
    {
        $subscribers = $this->getNotTriggeredSubscribers();
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
        }
    }

    public function getNotTriggeredSubscribers()
    {
        return $this->mailList->activeSubscribers()
                                      ->leftJoin('auto_triggers', function ($join) {
                                          $join->on('subscribers.id', '=', 'auto_triggers.subscriber_id')->where('auto_triggers.automation2_id', '=', $this->id);
                                      })
                                      ->whereNull('auto_triggers.id')->select('subscribers.*')->get();
    }

    public function allowApiCall()
    {
        // Usually invoked by API call
        $type = $this->getTriggerAction()->getOption('key');

        return $type == 'api-3-0';
    }
}
