<?php
namespace Flowpack\Neos\Matomo\Domain\Dto;

/*
 * This script belongs to the Neos CMS package "Flowpack.Neos.Matomo".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

class DeviceDataResult extends AbstractDataResult
{

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        $totalVisits = 0;
        $clientDevices = [
            'Desktop' => 0,
            'Tablet' => 0,
            'Smartphone' => 0
        ];

        foreach ($this->results as $year => $devices) {
            if (is_array($devices)) {
                foreach ($devices as $device) {
                    $label = $device['label'] ?? 'Desktop';
                    $nbVisits = $device['nb_visits'] ?? 0;
                    if (array_key_exists($label, $clientDevices)) {
                        $clientDevices[$label] += $nbVisits;
                    }
                    $totalVisits += $nbVisits;
                }
            }
        }

        return [
            'totals' => ['uniquePageviews' => $totalVisits],
            'rows' => [
                ['deviceCategory' => 'desktop', 'uniquePageviews' => $clientDevices['Desktop'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientDevices['Desktop'] * 100 / $totalVisits)))],
                ['deviceCategory' => 'tablet', 'uniquePageviews' => $clientDevices['Tablet'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientDevices['Tablet'] * 100 / $totalVisits)))],
                ['deviceCategory' => 'smartphone', 'uniquePageviews' => $clientDevices['Smartphone'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientDevices['Smartphone'] * 100 / $totalVisits)))]
            ]
        ];
    }
}
