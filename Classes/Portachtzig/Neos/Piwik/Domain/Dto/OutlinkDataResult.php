<?php
namespace Portachtzig\Neos\Piwik\Domain\Dto;

/*
 * This script belongs to the Neos CMS package "Portachtzig.Neos.Piwik".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

class OutlinkDataResult extends AbstractDataResult
{

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        $results = json_decode($this->response->getContent(), true);
        $totalVisits = 0;
        $visitedOutlinks = [];
        $allOutlinks = [];
        foreach ($results as $year => $devices) {
            if (is_array($devices)) {
                foreach ($devices as $device) {
                    $totalVisits = $totalVisits + $device['nb_hits'];

                }
                foreach ($devices as $device) {
                    $outlink = $device['label'];
                    $visitedOutlinks[$outlink] = 0;
                    $visitedOutlinks[$outlink] = $visitedOutlinks[$outlink] + $device['nb_hits'];
                    $allOutlinks[] = [
                        'outlinks' => $outlink,
                        'visits' => $visitedOutlinks[$outlink],
                        'percent' => ($totalVisits == 0 ? 0 : round(($visitedOutlinks[$outlink] * 100 / $totalVisits)))
                    ];
                }
            }
        }

        return [
            'totals' => ['visits' => $totalVisits],
            'rows' => $allOutlinks
        ];
    }
}
