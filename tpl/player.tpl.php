<?php

use phpOMS\DataStorage\Database\Query\Builder;

$playername = $request->getData('name');

$query = new Builder($db);
$query->raw(
    'SELECT elo.elo_datetime, elo.elo_elo
    FROM elo
    left join driver on elo.elo_driver = driver.driver_id
    WHERE driver.driver_name = \'' . $playername . '\'
    ORDER BY elo.elo_datetime ASC;'
);
$elos = $query->execute()->fetchAll();

$query = new Builder($db);
$query->raw(
    'SELECT glicko.glicko_datetime, glicko.glicko_elo
    FROM glicko
    left join driver on glicko.glicko_driver = driver.driver_id
    WHERE driver.driver_name = \'' . $playername . '\'
    ORDER BY glicko.glicko_datetime ASC;'
);
$glickos = $query->execute()->fetchAll();

$query = new Builder($db);
$query->raw(
    'SELECT glicko2.glicko2_datetime, glicko2.glicko2_elo
    FROM glicko2
    left join driver on glicko2.glicko2_driver = driver.driver_id
    WHERE driver.driver_name = \'' . $playername . '\'
    ORDER BY glicko2.glicko2_datetime ASC;'
);
$glicko2s = $query->execute()->fetchAll();
?>

<script src="/../../Resources/chartjs/chart.js"></script>

<canvas class="chart" data-chart='
{
    "type": "line",
    "data": {
        "labels": [
            <?php
            echo \implode(',', \array_map(function ($var) {return '"' . \date('Y-m-d', $var['glicko2_datetime']) . '"';}, $glicko2s));
            ?>
        ],
        "datasets": [
            {
                "label": "Elo",
                "data": [
                    <?php
                    echo \implode(',', \array_map(function ($var) {return \sprintf('%02d', $var['elo_elo']);}, $elos));
                    ?>
                ],
                "yAxisID": "y",
                "fill": false,
                "tension": 0.0,
                "borderColor": "rgb(54, 162, 235)",
                "backgroundColor": "rgb(54, 162, 235)"
            },
            {
                "label": "Glicko1",
                "data": [
                    <?php
                    echo \implode(',', \array_map(function ($var) {return \sprintf('%02d', $var['glicko_elo']);}, $glickos));
                    ?>
                ],
                "yAxisID": "y",
                "fill": false,
                "tension": 0.0,
                "borderColor": "rgb(46, 204, 113)",
                "backgroundColor": "rgb(46, 204, 113)"
            },
            {
                "label": "Glicko2",
                "data": [
                    <?php
                    echo \implode(',', \array_map(function ($var) {return \sprintf('%02d', $var['glicko2_elo']);}, $glicko2s));
                    ?>
                ],
                "yAxisID": "y",
                "fill": false,
                "tension": 0.0,
                "borderColor": "rgb(204, 46, 46)",
                "backgroundColor": "rgb(204, 46, 46)"
            }
        ]
    },
    "options": {
        "responsive": true,
        "scales": {
            "y": {
                "title": {
                    "display": true,
                    "text": "Rating"
                },
                "display": true,
                "position": "left"
            }
        },
        "plugins": {
            "title": {
                "display": true,
                "text": "<?= \htmlspecialchars($playername); ?>"
            }
        }
    }
}
'
></canvas>

<script>
const chart = document.getElementsByTagName('canvas')[0];
const data = JSON.parse(chart.getAttribute('data-chart'));
const myChart = new Chart(chart.getContext('2d'), data);
</script>
