<?php
namespace Flowpack\Neos\Matomo\Domain\Dto;

/*
 * This script belongs to the Neos CMS package "Flowpack.Neos.Matomo".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Http\Response;

abstract class AbstractDataResult implements \JsonSerializable
{

    /**
     * The response from Matomo containing serialized json data
     *
     * @var Response
     */
    protected $response;

    /**
     * The json decoded results
     *
     * @var array
     */
    protected $results;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;

        if ($response !== null) {
            $this->results = json_decode($this->response->getContent(), true);
        } else {
            $this->results = [];
        }
    }

    /**
     * @return array
     */
    function jsonSerialize() {
        return [];
    }

}