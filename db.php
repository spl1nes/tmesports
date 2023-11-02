<?php

include_once __DIR__ . '/../../phpOMS/Autoloader.php';

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\DataStorage\Database\Connection\SQLiteConnection;
use phpOMS\DataStorage\Database\DatabaseStatus;

class Driver
{
    public int $id = 0;
    public string $name = '';
}

class NullDriver extends Driver {}

class DriverMapper extends DataMapperFactory
{
    public const COLUMNS = [
        'driver_id'   => ['name' => 'driver_id',   'type' => 'int',    'internal' => 'id'],
        'driver_name' => ['name' => 'driver_name', 'type' => 'string', 'internal' => 'name'],
    ];

    public const TABLE = 'driver';
    public const PRIMARYFIELD = 'driver_id';
}

class Elo
{
    public int $id = 0;
    public int $datetime = 0;
    public int $driver = 0;
    public int $elo = 1500;
}

class NullElo extends Elo {}

class EloMapper extends DataMapperFactory
{
    public const COLUMNS = [
        'elo_id'     => ['name' => 'elo_id',     'type' => 'int',    'internal' => 'id'],
        'elo_datetime'    => ['name' => 'elo_datetime',    'type' => 'int',    'internal' => 'datetime'],
        'elo_driver' => ['name' => 'elo_driver', 'type' => 'int', 'internal' => 'driver'],
        'elo_elo'    => ['name' => 'elo_elo',    'type' => 'int',    'internal' => 'elo'],
    ];

    public const TABLE = 'elo';
    public const PRIMARYFIELD = 'elo_id';
}

class Glicko
{
    public int $id = 0;
    public int $datetime = 0;
    public int $driver = 0;
    public int $elo = 1500;
    public int $rd = 50;
    public int $last_match = 0;
}

class NullGlicko extends Glicko {}

class GlickoMapper extends DataMapperFactory
{
    public const COLUMNS = [
        'glicko_id'     => ['name' => 'glicko_id',     'type' => 'int',    'internal' => 'id'],
        'glicko_datetime'    => ['name' => 'glicko_datetime',    'type' => 'int',    'internal' => 'datetime'],
        'glicko_driver' => ['name' => 'glicko_driver', 'type' => 'int', 'internal' => 'driver'],
        'glicko_elo'    => ['name' => 'glicko_elo',    'type' => 'int',    'internal' => 'elo'],
        'glicko_rd'     => ['name' => 'glicko_rd',     'type' => 'int',    'internal' => 'rd'],
        'glicko_last_match'     => ['name' => 'glicko_last_match',     'type' => 'int',    'internal' => 'last_match'],
    ];

    public const TABLE = 'glicko';
    public const PRIMARYFIELD = 'glicko_id';
}

class Glicko2
{
    public int $id = 0;
    public int $driver = 0;
    public int $datetime = 0;
    public int $elo = 1500;
    public float $vol = 0.06;
    public int $rd = 50;
}

class NullGlicko2 extends Glicko2 {}

class Glicko2Mapper extends DataMapperFactory
{
    public const COLUMNS = [
        'glicko2_id'     => ['name' => 'glicko2_id',     'type' => 'int',    'internal' => 'id'],
        'glicko2_datetime' => ['name' => 'glicko2_datetime', 'type' => 'int', 'internal' => 'datetime'],
        'glicko2_driver' => ['name' => 'glicko2_driver', 'type' => 'int', 'internal' => 'driver'],
        'glicko2_vol'    => ['name' => 'glicko2_vol',    'type' => 'float',  'internal' => 'vol'],
        'glicko2_elo'    => ['name' => 'glicko2_elo',    'type' => 'int',    'internal' => 'elo'],
        'glicko2_rd'     => ['name' => 'glicko2_rd',     'type' => 'int',    'internal' => 'rd'],
    ];

    public const TABLE = 'glicko2';
    public const PRIMARYFIELD = 'glicko2_id';
}

class GameMatch
{
    public int $id = 0;
    public int $mid = 0;
    public int $driver = 0;
    public int $datetime = 0;
    public int $rank = 0;
    public string $event = '';
}

class NullGameMatch extends GameMatch {}

class GameMatchMapper extends DataMapperFactory
{
    public const COLUMNS = [
        'match_id'     => ['name' => 'match_id',     'type' => 'int',    'internal' => 'id'],
        'match_mid'    => ['name' => 'match_mid',    'type' => 'int',    'internal' => 'mid'],
        'match_driver' => ['name' => 'match_driver', 'type' => 'int', 'internal' => 'driver'],
        'match_datetime'  => ['name' => 'match_datetime',  'type' => 'int',    'internal' => 'datetime'],
        'match_rank'   => ['name' => 'match_rank',   'type' => 'int',    'internal' => 'rank'],
        'match_event'   => ['name' => 'match_event',   'type' => 'string',    'internal' => 'event'],
    ];

    public const TABLE = 'match';
    public const PRIMARYFIELD = 'match_id';
}

// DB connection
$db = new SQLiteConnection([
    'db' => 'sqlite',
    'database' => __DIR__ . '/tmesports.sqlite',
]);

$db->connect();

if ($db->getStatus() !== DatabaseStatus::OK) {
    exit;
}

DataMapperFactory::db($db);