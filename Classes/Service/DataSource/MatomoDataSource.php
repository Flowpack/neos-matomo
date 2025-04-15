<?php
declare(strict_types=1);

namespace Flowpack\Neos\Matomo\Service\DataSource;

/*
 * This script belongs to the Neos CMS package "Flowpack.Neos.Matomo".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\Neos\Matomo\Service\Reporting;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Flowpack\Neos\Matomo\Domain\Dto\ErrorDataResult;

class MatomoDataSource extends AbstractDataSource
{
    #[Flow\Inject]
    protected Reporting $reportingService;

    /**
     * @var string
     */
    static protected $identifier = 'flowpack-neos-matomo';

    /**
     * Get data
     *
     * {@inheritdoc}
     */
    public function getData(Node $node = NULL, array $arguments = []): array
    {
        if ($node === null) {
            return [];
        }
        $data = $this->reportingService->getNodeStatistics($node, $this->controllerContext->getRequest(), $arguments);

        if ($data instanceof ErrorDataResult) {
            return [
                'error' => $data
            ];
        }

        return [
            'data' => $data
        ];
    }
}
