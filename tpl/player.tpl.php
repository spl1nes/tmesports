<?php

use phpOMS\DataStorage\Database\Query\Builder;

$playername = $request->getData('name');

$query = new Builder($db);
$query->raw(
    'SELECT glicko2.glicko2_datetime, glicko2.glicko2_elo
    FROM glicko2
    left join driver on glicko2.glicko2_driver = driver.driver_id
    WHERE driver.driver_name = \'' . $playername . '\'
    ORDER BY glicko2.glicko2_datetime ASC;'
);
$elos = $query->execute()->fetchAll();
?>

<script src="/../../Resources/chartjs/chart.js"></script>

<canvas class="chart" data-chart='
{
    "type": "line",
    "data": {
        "labels": [
            <?php
            echo \implode(',', \array_map(function ($var) {return '"' . \date('Y-m-d', $var['glicko2_datetime']) . '"';}, $elos));
            ?>
        ],
        "datasets": [
            {
                "label": "<?= \htmlspecialchars($playername); ?>",
                "data": [
                    <?php
                    echo \implode(',', \array_map(function ($var) {return \sprintf('%02d', $var['glicko2_elo']);}, $elos));
                    ?>
                ],
                "yAxisID": "y",
                "fill": false,
                "tension": 0.0,
                "borderColor": "rgb(54, 162, 235)",
                "backgroundColor": "rgb(54, 162, 235)"
            }
        ]
    },
    "options": {
        "responsive": true,
        "scales": {
            "y": {
                "title": {
                    "display": true,
                    "text": "Elo"
                },
                "display": true,
                "position": "left"
            }
        }
    }
}
'
></canvas>

<table>
    <thead>
        <tr>
            <td>Date</td>
            <td>Rating</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($elos as $elo) : ?>
        <tr>
            <td><?= $elo['glicko2_datetime']; ?>
            <td><?= (int) $elo['glicko2_elo']; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
const chart = document.getElementsByTagName('canvas')[0];
const data = JSON.parse(chart.getAttribute('data-chart'));
const myChart = new Chart(chart.getContext('2d'), data);
</script>