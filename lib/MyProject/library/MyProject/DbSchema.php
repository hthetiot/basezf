<?php
/**
 * DbItem class in /MyProject/
 *
 * @category   MyProject
 * @package    MyProject_DbItem
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
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
                'example_type_id'   => 'INT2',
                'unique_string'     => 'VARCHAR(64)',
                'string'            => 'VARCHAR(255)',
                'state'             => 'INT2',
                'update'            => 'TIMESTAMP',
                'creation'          => 'TIMESTAMP',
            ),
        ),
    );
}
