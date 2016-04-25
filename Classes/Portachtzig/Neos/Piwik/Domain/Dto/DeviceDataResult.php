<?php
namespace Portachtzig\Neos\Piwik\Domain\Dto;

/*
 * This script belongs to the Neos CMS package "Portachtzig.Neos.Piwik".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;

class DeviceDataResult implements \JsonSerializable
{

    /**
     * The Piwik response, formatted as a json string
     *
     * @var string
     */
    protected $response;

    /**
     * @param string $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        $results = json_decode($this->response->getContent(), true);
        $totalVisits = 0;
        $clientDevices = array(
            'Desktop' => 0,
            'Tablet' => 0,
            'Smartphone' => 0
        );

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

        return array(
            'totals' => array('uniquePageviews' => $totalVisits),
            'rows' => array(
                array('deviceCategory' => 'desktop', 'uniquePageviews' => $clientDevices['Desktop'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientDevices['Desktop'] * 100 / $totalVisits)))),
                array('deviceCategory' => 'tablet', 'uniquePageviews' => $clientDevices['Tablet'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientDevices['Tablet'] * 100 / $totalVisits)))),
                array('deviceCategory' => 'smartphone', 'uniquePageviews' => $clientDevices['Smartphone'], 'percent' => ($totalVisits == 0 ? 0 : round(($clientDevices['Smartphone'] * 100 / $totalVisits))))
            )
        );
    }
}