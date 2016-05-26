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
class Project extends Model {

    /**
     * The table containing the projects data
     *
     * @var string
     */
    protected static $tablename = 'HTrackerProject';


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
        'name' => array(
            'type' => 'varchar(32)'
        ),
        'description' => array(
            'type' => 'text',
        ),
        'author' => array(
            'type' => 'int(11)'
        ),
        'ctime' => array(
            'type' => 'int(11)'
        ),
        'mtime' => array(
            'type' => 'int(11)'
        )
    );


    public function __construct($data = array()) {
        parent::__construct($data);
        if(!empty($this->timestamp)) {
            $this->timestamp = date('Y-m-d');
        }

    }
}
