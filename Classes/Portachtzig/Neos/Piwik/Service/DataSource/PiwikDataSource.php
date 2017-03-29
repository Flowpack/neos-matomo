<?php
namespace Portachtzig\Neos\Piwik\Service\DataSource;

/*
 * This script belongs to the Neos CMS package "Portachtzig.Neos.Piwik".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

class PiwikDataSource extends AbstractDataSource
{
    /**
     * @Flow\Inject
     * @var \Portachtzig\Neos\Piwik\Service\Reporting
     */
    protected $reportingService;

    /**
     * @var string
     */
    static protected $identifier = 'portachtzig-neos-piwik';

    /**
     * Get data
     *
     * {@inheritdoc}
     */
    public function getData(NodeInterface $node = NULL, array $arguments)
    {
        $piwikData = $this->reportingService->getNodeStatistics($node, $this->controllerContext, $arguments);
        $data = array(
            'data' => $piwikData
        );

        return $data;
    }

}
