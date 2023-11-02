<?php

include __DIR__ . '/../db.php';

use phpOMS\Algorithm\Rating\Elo as RatingElo;
use phpOMS\Algorithm\Rating\Glicko1;
use phpOMS\Algorithm\Rating\Glicko2 as RatingGlicko2;

$lastDateTime = 0;
$MAX_ELO_CHANGE = 300;

function createResultMatrixForRating($driver, $drivers) {
    $result = [];
    foreach ($drivers as $match) {
        if ($driver->id === $match->id) {
            continue;
        }

        if ($driver->rank < $match->rank) {
            $result[$match->driver] = 1;
        } elseif ($driver->rank === $match->rank) {
            $result[$match->driver] = 0.5;
        } else {
            $result[$match->driver] = 0;
        }
    }

    return $result;
}

function getOps($driver, $drivers) {
    $ops = [];
    foreach ($drivers as $id => $d) {
        if ($driver === $id) {
            continue;
        }

        $ops[$id] = $d;
    }

    return $ops;
}

$eloAlgorithm = new RatingElo();
$glickoAlgorithm = new Glicko1();
$glicko2Algorithm = new RatingGlicko2();

// We assume there are not more than 8 concurrent matches of maximum of 16 players in one match
while (!empty($matches = GameMatchMapper::getAll()
    ->where('datetime', $lastDateTime, '>')
    ->sort('datetime', 'ASC')
    ->limit(16 * 8)
    ->execute())
) {
    $lastDateTime = \reset($matches)->datetime;

    $matchGroups = [];

    foreach ($matches as $key => $match) {
        // Don't allow datetime switch (that happens in the next iteration)
        if ($match->datetime !== $lastDateTime) {
            unset($matches[$key]);

            continue;
        }

        // One match has multiple players & there can be multiple matches at a given datetime
        $matchGroups[$match->mid][$match->driver] = $match;
    }

    $driverIds = [];

    $elo = [];
    $glicko = [];
    $glicko2 = [];

    foreach ($matches as $match) {
        $driverIds[] = $match->driver;

        // Set up ratings incl. default ratings if not available
        $elo[$match->driver] = EloMapper::get()
            ->where('driver', $match->driver)
            ->where('datetime', $lastDateTime, '<=')
            ->sort('datetime', 'DESC')
            ->limit(1)
            ->execute();

        if ($elo[$match->driver]->id === 0) {
            $rating = new Elo();
            $rating->driver = $match->driver;
            $rating->datetime = 0;
            EloMapper::create()->execute($rating);
            $elo[$match->driver] = $rating;
        }

        $glicko[$match->driver] = GlickoMapper::get()
            ->where('driver', $match->driver)
            ->where('datetime', $lastDateTime, '<=')
            ->sort('datetime', 'DESC')
            ->limit(1)
            ->execute();

        if ($glicko[$match->driver]->id === 0) {
            $rating = new Glicko();
            $rating->driver = $match->driver;
            $rating->datetime = 0;
            GlickoMapper::create()->execute($rating);
            $glicko[$match->driver] = $rating;
        }

        $glicko2[$match->driver] = Glicko2Mapper::get()
            ->where('driver', $match->driver)
            ->where('datetime', $lastDateTime, '<=')
            ->sort('datetime', 'DESC')
            ->limit(1)
            ->execute();

        if ($glicko2[$match->driver]->id === 0) {
            $rating = new Glicko2();
            $rating->driver = $match->driver;
            $rating->datetime = 0;
            Glicko2Mapper::create()->execute($rating);
            $glicko2[$match->driver] = $rating;
        }
    }

    // There can be multiple matches at a given datetime
    foreach ($matchGroups as $mid => $matches) {
        $eloTemp = [];
        $glickoTemp = [];
        $glicko2Temp = [];

        foreach ($matches as $match) {
            $eloTemp[$match->driver] = $elo[$match->driver];
            $glickoTemp[$match->driver] = $glicko[$match->driver];
            $glicko2Temp[$match->driver] = $glicko2[$match->driver];
        }

        foreach ($matches as $match) {
            $results = createResultMatrixForRating($match, $matches);

            // Handle elo
            $ops = getOps($match->driver, $eloTemp);
            $rating = $eloAlgorithm->rating(
                $eloTemp[$match->driver]->elo,
                \array_map(function ($vars) {
                    return $vars->elo;
                }, $ops),
                $results
            );

            $newRating = new Elo();
            $newRating->driver = $match->driver;
            $newRating->datetime = $lastDateTime;
            $newRating->elo = (int) \max(\min($rating['elo'], $eloTemp[$match->driver]->elo + $MAX_ELO_CHANGE), $eloTemp[$match->driver]->elo - $MAX_ELO_CHANGE);
            EloMapper::create()->execute($newRating);

            // Handle glicko
            $ops = getOps($match->driver, $glickoTemp);
            $rating = $glickoAlgorithm->rating(
                $glickoTemp[$match->driver]->elo,
                $glickoTemp[$match->driver]->rd,
                $glickoTemp[$match->driver]->last_match,
                (int) ($lastDateTime / (60 * 60 * 24)),
                \array_map(function ($vars) {
                    return $vars->elo;
                }, $ops),
                $results,
                \array_map(function ($vars) {
                    return $vars->rd;
                }, $ops),
            );

            $newRating = new Glicko();
            $newRating->driver = $match->driver;
            $newRating->datetime = $lastDateTime;
            $newRating->elo = (int) \max(\min($rating['elo'], $glickoTemp[$match->driver]->elo + $MAX_ELO_CHANGE), $glickoTemp[$match->driver]->elo - $MAX_ELO_CHANGE);
            $newRating->rd = $rating['rd'];
            $newRating->last_match = (int) ($lastDateTime / (60 * 60 * 24));
            GlickoMapper::create()->execute($newRating);

            // Handle glicko2
            $ops = getOps($match->driver, $glicko2Temp);
            $rating = $glicko2Algorithm->rating(
                $glicko2Temp[$match->driver]->elo,
                $glicko2Temp[$match->driver]->rd,
                $glicko2Temp[$match->driver]->vol,
                \array_map(function ($vars) {
                    return $vars->elo;
                }, $ops),
                $results,
                \array_map(function ($vars) {
                    return $vars->rd;
                }, $ops),
            );

            $newRating = new glicko2();
            $newRating->driver = $match->driver;
            $newRating->datetime = $lastDateTime;
            $newRating->elo = (int) \max(\min($rating['elo'], $glicko2Temp[$match->driver]->elo + $MAX_ELO_CHANGE), $glicko2Temp[$match->driver]->elo - $MAX_ELO_CHANGE);
            $newRating->rd = $rating['rd'];
            $newRating->vol = $rating['vol'];
            Glicko2Mapper::create()->execute($newRating);
        }
    }
}
