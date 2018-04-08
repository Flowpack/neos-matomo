<?php
namespace Flowpack\Neos\Matomo\Domain\Dto;

/*
 * This script belongs to the Neos CMS package "Flowpack.Neos.Matomo".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

class TimeSeriesDataResult extends AbstractDataResult
{

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        $i = 0;
        $totalVisits = 0;
        $totalHits = 0;
        $rows = [];

        foreach ($this->results as $key => $value) {
            if (!empty($value) && is_array($value)) {
                $rows[$i]['date'] = $key;
                $rows[$i]['nb_visits'] = $value[0]['nb_visits'];

                $totalVisits = $totalVisits + $value[0]['nb_visits'];
                $totalHits = $totalHits + $value[0]['nb_hits'];
            } else {
                $rows[$i]['date'] = $key;
                $rows[$i]['nb_visits'] = 0;
            }
            $i++;
        }

        return [
            'totals' => ['nb_visits' => $totalVisits, 'nb_hits' => $totalHits],
            'rows' => $rows
        ];
    }
}
