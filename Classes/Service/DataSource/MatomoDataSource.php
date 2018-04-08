<?php
namespace Flowpack\Neos\Matomo\Service\DataSource;

/*
 * This script belongs to the Neos CMS package "Flowpack.Neos.Matomo".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class MatomoDataSource extends AbstractDataSource
{
    /**
     * @Flow\Inject
     * @var \Flowpack\Neos\Matomo\Service\Reporting
     */
    protected $reportingService;

    /**
     * @var string
     */
    static protected $identifier = 'flowpack-neos-matomo';

    /**
     * Get data
     *
     * {@inheritdoc}
     */
    public function getData(NodeInterface $node = NULL, array $arguments)
    {
        $data = $this->reportingService->getNodeStatistics($node, $this->controllerContext, $arguments);

        return [
            'data' => $data
        ];
    }

}
