<?php
namespace Portachtzig\Neos\Piwik\Exception;

/*
 * This script belongs to the Neos CMS package "Portachtzig.Neos.Piwik".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Portachtzig\Neos\Piwik\Exception;

/**
 * Analytics are not available (e.g. node is not yet published)
 */
class StatisticsNotAvailableException extends Exception
{

    /**
     * @var integer
     */
    protected $statusCode = 404;

}
