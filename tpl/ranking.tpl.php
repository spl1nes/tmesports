<?php

use phpOMS\DataStorage\Database\Query\Builder;

$query = new Builder($db);
$query->raw(
    'SELECT driver.driver_name, glicko2.glicko2_elo
    FROM glicko2
    left join driver on glicko2.glicko2_driver = driver.driver_id
    WHERE glicko2.glicko2_datetime = (
        SELECT MAX(glicko2_datetime)
        FROM glicko2
        WHERE glicko2.glicko2_driver = driver.driver_id
    )
    GROUP BY driver.driver_name
    ORDER BY glicko2.glicko2_elo DESC;'
);
$drivers = $query->execute()->fetchAll();
?>
<table>
    <thead>
        <tr>
            <td>Rank</td>
            <td>Name</td>
            <td>Rating</td>
        </tr>
    </thead>
    <tbody>
        <?php $rank = 0; foreach ($drivers as $driver) : ++$rank; ?>
        <tr>
            <td><?= $rank; ?>
            <td><?= \htmlspecialchars($driver['driver_name']); ?>
            <td><?= (int) $driver['glicko2_elo']; ?>
        <?php endforeach; ?>
    </tbody>
</table>
