<?php
namespace App\Helpers;

use function _\kebabCase;
use Medoo\Medoo as MedooDB; // for columns to work

class DB
{

    public $db;

    public function __construct()
    {

        $this->db = new MedooDB([
            // [required]
            'type'     => 'mysql',
            'host'     => 'localhost',
            'database' => $_ENV['DB_DATABASE'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],

            // [optional]
            // 'charset' => 'utf8mb4',
            // 'collation' => 'utf8mb4_general_ci',
            'port'     => $_ENV['DB_PORT'],

            // [optional] The table prefix. All table names will be prefixed as PREFIX_table.
            'prefix'   => $_ENV['DB_PREFIX'],

            // [optional] To enable logging. It is disabled by default for better performance.
            'logging'  => $_ENV['DB_LOGGING'],

            // [optional]
            // Error mode
            // Error handling strategies when the error has occurred.
            // PDO::ERRMODE_SILENT (default) | PDO::ERRMODE_WARNING | PDO::ERRMODE_EXCEPTION
            // Read more from https://www.php.net/manual/en/pdo.error-handling.php.
            // 'error' => PDO::ERRMODE_SILENT,

            // [optional]
            // The driver_option for connection.
            // Read more from http://www.php.net/manual/en/pdo.setattribute.php.
            // 'option' => [
            //     PDO::ATTR_CASE => PDO::CASE_NATURAL
            // ],

            // [optional] Medoo will execute those commands after the database is connected.
            // 'command' => [
            //     'SET SQL_MODE=ANSI_QUOTES'
            // ]
        ]);
        $timezone = $_ENV['MYSQL_TIMEZONE'];
        $this->db->pdo->exec("SET time_zone = '$timezone'");
    }

    public function get_or_create_id($table, $args)
    {
        $id = $this->db->get($table, 'id', $args);

        if (! $id) {

            $dbconn = $this->db;
            $dbconn->insert($table, $args);
            $id = $dbconn->id();
        }
        return $id;

    }

    public function get_option($table, $option)
    {
        if (empty($option)) {
            return null;
        }
        file_put_contents(__DIR__.'/./x.txt', json_encode(['slug' => $option]));
        $id = $this->db->get('o_' . $table, 'id', ['slug' => trim($option)]);
        if (! $id) {
            return -1;
        }
        return $id;
    }

    public function get_or_make_option($table, $option)
    {
        if (empty($option)) {
            return null;
        }
        $id = $this->db->get('o_' . $table, 'id', ['text' => $option]);
        if (! $id) {
            $args['text'] = $option;
            $args['slug'] = kebabCase($option);
            $this->db->insert('o_' . $table, $args);
            $id = $this->db->id();
        }
        return $id;
    }

    public function get_or_make_contact($name, $mobile)
    {

        $where = [
            'cname'   => $name,
            'cmobile' => $mobile,
        ];

        $id = $this->db->get('profile_contacts', 'id', $where);

        if (! $id) {

            $this->db->insert('profile_contacts', $where);

            $id = $this->db->id();

        }

        return $id;

    }

    public function create_slug($table, $title)
    {
        $random = new \App\Helpers\Random;
        $slug   = null;
        $loop   = 1;
        $char   = 4;
        do {
            $slug = $random->slug($title, $char);

            $count = $this->db->count($table, ['slug' => $slug]);

            if ($count) {

                $loop++;
            }
            if ($loop > 99) {

                $loop = 1;
                $char = 5;
            }

        } while ($count);

        return $slug;
    }

}
