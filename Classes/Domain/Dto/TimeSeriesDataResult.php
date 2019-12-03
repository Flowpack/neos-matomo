<?php
declare(strict_types=1);

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
    public function jsonSerialize(): array
    {
        $i = 0;
        $totalVisits = 0;
        $totalHits = 0;
        $rows = [];

        foreach ($this->results as $key => $value) {
            if (!empty($value) && is_array($value)) {
                $nbVisits = $value[0]['nb_visits'] ?? 0;
                $rows[$i]['date'] = $key;
                $rows[$i]['nb_visits'] = $nbVisits;

                $totalVisits += $nbVisits;
                $totalHits += ($value[0]['nb_hits'] ?? 0);
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
