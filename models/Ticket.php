<?php
/**
 * Ticket.php
 *
 * @author Elvyrra SAS <lecocq.elvyrra@gmail.com>
 */
namespace Hawk\Plugins\HTracker;

/**
 * This model manages the tickets in the plugin h-tracker
 */
class Ticket extends Model {
    /**
     * The table containing the tickets data
     *
     * @var string
     */
    protected static $tablename = 'HTrackerTicket';

    /**
     * The table fields
     *
     * @var array
     */
    protected static $fields = array(
        'id' => array(
            'type' => 'INT(11)',
            'auto_increment' => true
        ),

        'projectId' => array(
            'type' => 'INT(11)'
        ),

        'title' => array(
            'type' => 'VARCHAR(256)'
        ),

        'description' => array(
            'type' => 'TEXT'
        ),

        'status' => array(
            'type' => 'VARCHAR(32)'
        ),

        'author' => array(
            'type' => 'INT(11)'
        ),

        // The target time
        'target' => array(
            'type' => 'INT(11)'
        ),

        'priority' => array(
            'type' => 'TINYINT(1)'
        ),

        'deadLine' => array(
            'type' => 'DATE'
        ),

        'ctime' => array(
            'type' => 'INT(11)'
        ),

        'mtime' => array(
            'type' => 'INT(11)'
        )
    );

    /**
     * The table constraints
     */
    protected static $constraints = array(
        'projectId' => array(
            'type' => 'foreign',
            'fields' => array(
                'projectId'
            ),
            'references' => array(
                'model' => 'Project',
                'fields' => array(
                    'id'
                )
            ),
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        )
    );

    /**
     * Critical priority
     */
    const PRIORITY_CRITICAL = 0;

    /**
     * Urgent priority
     */
    const PRIORITY_URGENT = 1;

    /**
     * Major priority
     */
    const PRIORITY_MAJOR = 2;

    /**
     * High priority
     */
    const PRIORITY_HIGH = 3;

    /**
     * Medium priority
     */
    const PRIORITY_MEDIUM = 4;

    /**
     * Low priority
     */
    const PRIORITY_LOW = 5;

    /**
     * Non urgent priority
     */
    const PRIORITY_NONURGENT = 6;

    /**
     * Postponed priority
     */
    const PRIORITY_POSTPONED = 7;

    /**
     * id of the status 'new'
     */
    const STATUS_NEW_ID = 1;

    /**
     * Id of the status 'closed'
     */
    const STATUS_CLOSED_ID = 2;


    public static function getPrioritiesList() {
        $priorities = array(
            self::PRIORITY_CRITICAL,
            self::PRIORITY_URGENT,
            self::PRIORITY_MAJOR,
            self::PRIORITY_HIGH,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_LOW,
            self::PRIORITY_NONURGENT,
            self::PRIORITY_POSTPONED,
        );

        return array_map(
            function ($priority) {
                return Lang::get(Plugin::current()->getName().'.ticket-priority-'.$priority);
            },
            $priorities
        );
    }

    /**
     * Check if the task is over delayed. A task is over delayed if it deadline is eralier today, and it status not set to
     * 'closed'
     * @returns boolean
     */
    public function isLate() {
        return (int) $this->status !== self::STATUS_CLOSED_ID && date('Y-m-d') > $this->deadLine;
    }


    /**
     * Save the ticket in the database
     */
    public function save() {
        $plugin = Plugin::current();
        $sendNotif = false;
        $project = Project::getById($this->projectId);
        $author = App::session()->getUser()->username;

        if(empty($this->id)) {
            parent::save();

            // creating a new ticket
            $sendNotif = true;
            $subject = Lang::get($plugin->getName() . '.notif-new-task', array(
                'project' => $project->name
            ));
            $content = View::make($plugin->getView('notifications/new-task.tpl'), array(
                'author' => $author,
                'project' => $project->name,
                'title' => $this->title,
                'ticketId' => $this->id
            ));
        }
        else {
            // Updating the ticket
            $oldValues = Ticket::getByExample(new DBExample(array(
                'id' =>$this->id
            )));

            if($oldValues) {
                $comments = array();
                foreach(array('title', 'description') as $key){
                    if($oldValues->$key !== $this->$key) {
                        $comments[] = Lang::get($plugin->getName() . '.'.$key.'-change-comment', array(
                            'oldValue' => $oldValues->$key,
                            'newValue' => $this->$key
                        ));
                    }
                }

                if($oldValues->deadLine !== $this->deadLine) {
                    $comments[] = Lang::get(
                        $plugin->getName() . '.deadLine-change-comment',
                        array(
                            'oldValue' => date(Lang::get('main.date-format'), strtotime($oldValues->deadLine)),
                            'newValue' => date(Lang::get('main.date-format'), strtotime($this->deadLine)),
                        )
                    );
                }

                if($oldValues->status !== $this->status) {
                    $statusList = array();
                    foreach(json_decode(Option::get($plugin->getName() . '.status')) as $status) {
                        $statusList[$status->id] = $status->label;
                    }

                    $comments[] = Lang::get(
                        $plugin->getName() . '.status-change-comment',
                        array(
                            'oldValue' => $statusList[$oldValues->status],
                            'newValue' => $statusList[$this->status]
                        )
                    );
                }

                if($oldValues->target !== $this->target) {
                    $comments[] = Lang::get(
                        $plugin->getName() . '.target-change-comment',
                        array(
                            'newValue' => empty($users[$this->target]) ? '-' : $users[$this->target]
                        )
                    );
                }

                if(!empty($comments)) {
                    TicketComment::add(array(
                        'author' => App::session()->getUser()->id,
                        'ticketId' => $this->id,
                        'ctime' => time(),
                        'description' => implode('<br />', $comments),
                    ));

                    $sendNotif = true;
                    $subject = Lang::get($plugin->getName() . '.notif-task-update', array(
                        'project' => $project->name,
                        'id' => $this->id,
                        'author' => $author
                    ));

                    $content = View::make($plugin->getView('notifications/task-modification.tpl'), array(
                        'author' => $author,
                        'title' => $this->title,
                        'ticketId' => $this->id
                    ));
                }
            }

            parent::save();
        }

        // Send the notification of new ticket / ticket modification
        if($sendNotif) {
            $recipients = array_filter(User::getAll('id'), function($user) use($plugin) {
                return  $user->isAllowed($plugin->getName() . '.manage-ticket') &&
                        // $user->id !== App::session()->getUser()->id;
                    true;
            });

            foreach($recipients as $recipient) {
                $email = new Mail();
                $email  ->subject($subject)
                        ->content($content)
                        ->to($recipient->email)
                        ->send();
            }
        }
    }
}
