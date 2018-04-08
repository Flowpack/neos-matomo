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
        $results = json_decode($this->response->getContent(), true);
        $totalVisits = 0;
        $clientDevices = [
            'Desktop' => 0,
            'Tablet' => 0,
            'Smartphone' => 0
        ];

        foreach ($results as $year => $devices) {
            if (is_array($devices)) {
                foreach ($devices as $device) {
                    if ($device['label'] == 'Desktop') {
                        $clientDevices['Desktop'] = $clientDevices['Desktop'] + $device['nb_visits'];
                    }
                    if ($device['label'] == 'Tablet') {
                        $clientDevices['Tablet'] = $clientDevices['Tablet'] + $device['nb_visits'];
                    }
                    if ($device['label'] == 'Smartphone') {
                        $clientDevices['Smartphone'] = $clientDevices['Smartphone'] + $device['nb_visits'];
                    }
                    $totalVisits = $totalVisits + $device['nb_visits'];
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
