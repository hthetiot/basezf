<?php
/**
 * DbItem class in /MyProject/
 *
 * @category   MyProject_Core
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

class MyProject_DbSchema
{
    public static $tables = array(
        
        /**
         * Table Example
         */
        'example' => array(
            'primary' => 'example_id',
            'foreign' => array(),
            'fields' => array(
                'example_id'        => 'SERIAL',
                'country_id'        => 'INT2',
                'language_id'       => 'INT2',
                'login'             => 'VARCHAR(64)',
                'email'             => 'VARCHAR(255)',
                'display_name'      => 'VARCHAR(64)',
                'creation'          => 'TIMESTAMP',
            ),
        ),
    );
}
