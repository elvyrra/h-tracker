<?php
/**
 * Project.php
 *
 * @author Elvyrra SAS <lecocq.elvyrra@gmail.com>
 */
namespace Hawk\Plugins\HTracker;

/**
 * This model describes the projects managed in the plugin h-tracker
 */

/**
 * This model describes the ticket comments in the plugin h-tracker
 */
class TicketComment extends Model {
    /**
     * The table containing the comments data
     * @var string
     */
    protected static $tablename = 'HTrackerTicketComment';


    /**
     * The table fields
     *
     * @var array
     */
    protected static $fields = array(
        'id' => array(
            'type' => 'int(11)',
            'auto_increment' => true
        ),

        'ticketId' => array(
            'type' => 'int(11)'
        ),

        'description' => array(
            'type' => 'text'
        ),

        'author' => array(
            'type' => 'int(11)'
        ),

        'ctime' => array(
            'type' => 'int(11)'
        )
    );

    /**
     * The table constraints
     *
     * @var array
     */
    protected static $constraints = array(
        'ticketId' => array(
            'type' => 'foreign',
            'fields' => array(
                'ticketId'
            ),
            'references' => array(
                'model' => 'Ticket',
                'fields' => array(
                    'id'
                )
            ),
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        )
    );


    public function __construct($data = array()) {
        parent::__construct($data);
        if(!empty($this->timestamp)) {
            $this->timestamp = date('Y-m-d');
        }
    }
}
