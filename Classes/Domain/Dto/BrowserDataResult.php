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

class BrowserDataResult extends AbstractDataResult
{
    /**
     * {@inheritdoc}
     */
    function jsonSerialize(): array
    {
        $totalVisits = 0;
        $clientBrowser = [];
        $allBrowser = [];
        foreach ($this->results as $year => $devices) {
            if (is_array($devices)) {
                foreach ($devices as $device) {
                    $nbVisits = $device['nb_visits'] ?? 0;
                    $totalVisits += $nbVisits;
                }
                foreach ($devices as $device) {
                    $nbVisits = $device['nb_visits'] ?? 0;
                    $browser = $device['label'] ?? '';
                    $clientBrowser[$browser] = 0;
                    $clientBrowser[$browser] += $nbVisits;
                    $allBrowser[] = [
                        'browsers' => $browser,
                        'uniquePageviews' => $clientBrowser[$browser],
                        'percent' => ($totalVisits == 0 ? 0 : round(($clientBrowser[$browser] * 100 / $totalVisits)))
                    ];
                }
            }
        }
        return [
            'totals' => ['uniquePageviews' => $totalVisits],
            'rows' => $allBrowser
        ];
    }

}
