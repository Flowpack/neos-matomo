<?php

namespace Flowpack\Neos\Matomo\Domain\Dto;

/*
 * This script belongs to the Neos CMS package "Flowpack.Neos.Matomo".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

class OperatingSystemDataResult extends AbstractDataResult
{

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        $totalVisits = 0;
        $clientOperatingSystems = [
            'GNU/Linux' => 0,
            'iOS' => 0,
            'Apple' => 0,
            'Windows' => 0,
            'Android' => 0
        ];

        foreach ($this->results as $year => $devices) {
            if (is_array($devices)) {
                foreach ($devices as $device) {
                    if ($device['label'] == 'GNU/Linux') {
                        $clientOperatingSystems['GNU/Linux'] = $clientOperatingSystems['GNU/Linux'] + $device['nb_visits'];
                    }
                    if ($device['label'] == 'iOS') {
                        $clientOperatingSystems['iOS'] = $clientOperatingSystems['iOS'] + $device['nb_visits'];
                    }
                    if ($device['label'] == 'Apple') {
                        $clientOperatingSystems['Apple'] = $clientOperatingSystems['Apple'] + $device['nb_visits'];
                    }
                    if ($device['label'] == 'Windows') {
                        $clientOperatingSystems['Windows'] = $clientOperatingSystems['Windows'] + $device['nb_visits'];
                    }
                    if ($device['label'] == 'Android') {
                        $clientOperatingSystems['Android'] = $clientOperatingSystems['Android'] + $device['nb_visits'];
                    }
                    $totalVisits = $totalVisits + $device['nb_visits'];
                }
            }
        }
        return [
            'totals' => ['uniquePageviews' => $totalVisits],
            'rows' => [
                ['osFamilies' => 'Apple', 'uniquePageviews' => $clientOperatingSystems['Apple'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientOperatingSystems['Apple'] * 100 / $totalVisits)))],
                ['osFamilies' => 'iOS', 'uniquePageviews' => $clientOperatingSystems['iOS'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientOperatingSystems['iOS'] * 100 / $totalVisits)))],
                ['osFamilies' => 'Windows', 'uniquePageviews' => $clientOperatingSystems['Windows'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientOperatingSystems['Windows'] * 100 / $totalVisits)))],
                ['osFamilies' => 'GNU/Linux', 'uniquePageviews' => $clientOperatingSystems['GNU/Linux'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientOperatingSystems['GNU/Linux'] * 100 / $totalVisits)))],
                ['osFamilies' => 'Android', 'uniquePageviews' => $clientOperatingSystems['Android'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientOperatingSystems['Android'] * 100 / $totalVisits)))]
            ]
        ];
    }

}
