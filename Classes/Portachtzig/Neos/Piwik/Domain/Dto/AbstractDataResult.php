<?php

namespace Portachtzig\Neos\Piwik\Domain\Dto;

/*
 * This script belongs to the Neos CMS package "Portachtzig.Neos.Piwik".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

abstract class AbstractDataResult implements \JsonSerializable
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
     * @return array
     */
    function jsonSerialize() {
        return [];
    }

}
