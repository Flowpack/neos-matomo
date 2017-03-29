<?php
namespace Portachtzig\Neos\Piwik\Domain\Dto;

/*
 * This script belongs to the Neos CMS package "Portachtzig.Neos.Piwik".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

class ColumnDataResult extends AbstractDataResult
{
    
    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        $results = json_decode($this->response->getContent(), true);
        $i = 0;
        $totalVisits = 0;
        $totalHits = 0;
        foreach ($results as $key => $value) {
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

        return array(
            'totals' => array('nb_visits' => $totalVisits, 'nb_hits' => $totalHits),
            'rows' => array(array('nb_visits' => $totalVisits, 'nb_hits' => $totalHits)),
        );
    }
}
