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
}
