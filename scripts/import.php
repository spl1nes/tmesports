<?php

include __DIR__ . '/../db.php';

$row = 0;
if (($handle = \fopen(__DIR__ . '/tm.csv', 'r')) === false) {
    exit;
}

while (($data = \fgetcsv($handle, 4096, ',')) !== false) {
    ++$row;

    if ($row === 1) {
        continue;
    }

    $mid   = $row;
    $datetime  = new \DateTime($data[0]);
    $event = $data[101];

    for ($i = 1; $i < 101; ++$i) {
        $driverName = \trim($data[$i]);

        if ($driverName === '') {
            continue;
        }

        // handle driver
        /** @var Driver $driver */
        $driver = DriverMapper::get()->where('name', $driverName)->execute();
        if ($driver->id === 0) {
            $driver = new Driver();
            $driver->name = $driverName;

            DriverMapper::create()->execute($driver);
        }

        // handle match
        $match = new GameMatch();
        $match->driver = $driver->id;
        $match->mid = $mid;
        $match->rank = $i;
        $match->datetime = $datetime->getTimestamp();
        $match->event = $event;

        GameMatchMapper::create()->execute($match);
    }
}
