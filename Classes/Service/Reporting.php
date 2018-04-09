<?php

namespace Flowpack\Neos\Matomo\Service;

/*
 * This script belongs to the Neos CMS package "Flowpack.Neos.Matomo".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\Neos\Matomo\Exception\StatisticsNotAvailableException;
use Flowpack\Neos\Matomo\Domain\Dto\AbstractDataResult;
use Flowpack\Neos\Matomo\Domain\Dto\TimeSeriesDataResult;
use Flowpack\Neos\Matomo\Domain\Dto\ColumnDataResult;
use Flowpack\Neos\Matomo\Domain\Dto\DeviceDataResult;
use Flowpack\Neos\Matomo\Domain\Dto\OperatingSystemDataResult;
use Flowpack\Neos\Matomo\Domain\Dto\BrowserDataResult;
use Flowpack\Neos\Matomo\Domain\Dto\OutlinkDataResult;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Response;
use Neos\Flow\Http\Uri;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Http\Client\Browser;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Service\Controller\AbstractServiceController;

/**
 * Class Reporting
 * @package Flowpack\Neos\Matomo\Service
 */
class Reporting extends AbstractServiceController
{

    /**
     * @Flow\Inject
     * @var CurlEngine
     */
    protected $browserRequestEngine;

    /**
     * @Flow\Inject
     * @var \Neos\Neos\Service\LinkingService
     */
    protected $linkingService;

    /**
     * @Flow\Inject
     * @var \Neos\ContentRepository\Domain\Service\ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var Browser
     */
    protected $browser;

    /**
     * Call the Matomo Reporting API
     *
     * @param $methodName string the method that should be called e.g. 'SitesManager.getAllSites'
     * @param $arguments array that contains the httpRequest arguments for the apiCall
     * @return array
     */
    public function callAPI($methodName, $arguments = [])
    {
        if (!empty($this->settings['host']) && !empty($this->settings['token_auth'] && !empty($this->settings['token_auth']))) {
            $apiCallUrl = $this->buildApiCallUrl(array_merge($arguments, ['method' => $methodName]));
            $response = $this->request($apiCallUrl);
            return json_decode($response->getContent(), TRUE);
        }
        return [];
    }

    /**
     * Call the Matomo Reporting API for node specific statistics
     *
     * @param $node NodeInterface
     * @param $controllerContext ControllerContext
     * @param $arguments array
     * @return AbstractDataResult
     */
    public function getNodeStatistics($node = NULL, $controllerContext = NULL, $arguments = [])
    {
        if (!empty($this->settings['host']) && !empty($this->settings['token_auth'] && !empty($this->settings['token_auth']))) {
            try {
                $pageUrl = urlencode($this->getLiveNodeUri($node, $controllerContext)->__toString());
            } catch (\Exception $e) {
                $this->systemLogger->log($e->getMessage(), LOG_WARNING);
                return null;
            }

            $arguments = array_filter($arguments, function($value, $key) {
                return !empty($value) && !in_array($key, ['view', 'device', 'type']);
            }, ARRAY_FILTER_USE_BOTH);

            if (in_array($arguments['view'], ['device', 'osFamilies', 'browsers', 'outlinks'])) {
                $arguments['segment'] = 'pageUrl==' . $pageUrl;
            }

            $arguments['pageUrl'] = $pageUrl;
            $apiCallUrl = $this->buildApiCallUrl($arguments);
            $response = $this->request($apiCallUrl);

            switch ($arguments['view']) {
                case 'TimeSeriesView':
                    return new TimeSeriesDataResult($response);
                    break;
                case 'ColumnView':
                    return new ColumnDataResult($response);
                    break;
                case 'device':
                    return new DeviceDataResult($response);
                    break;
                case 'osFamilies':
                    return new OperatingSystemDataResult($response);
                    break;
                case 'browsers':
                    return new BrowserDataResult($response);
                    break;
                case 'outlinks':
                    return new OutlinkDataResult($response);
                    break;
            }
        }
        return null;
    }

    /**
     * Resolve an URI for the given node in the live workspace (this is where analytics usually are collected)
     *
     * @param NodeInterface $node
     * @param ControllerContext $controllerContext
     * @return Uri
     * @throws StatisticsNotAvailableException If the node was not yet published and no live workspace URI can be resolved
     * @throws \Exception
     */
    protected function getLiveNodeUri(NodeInterface $node, ControllerContext $controllerContext)
    {
        $contextProperties = $node->getContext()->getProperties();
        $contextProperties['workspaceName'] = 'live';
        $liveContext = $this->contextFactory->create($contextProperties);
        $liveNode = $liveContext->getNodeByIdentifier($node->getIdentifier());
        if ($liveNode === NULL) {
            throw new StatisticsNotAvailableException('Matomo Statistics are only available on a published node', 1445812693);
        }
        $nodeUriString = $this->linkingService->createNodeUri($controllerContext, $liveNode, NULL, 'html', TRUE);
        $nodeUri = new Uri($nodeUriString);

        return $nodeUri;
    }


    /**
     * Send a request via curl to the api endpoint and returns the response
     *
     * @param string $apiCallUrl
     * @return Response
     */
    protected function request($apiCallUrl)
    {
        $this->browserRequestEngine->setOption(CURLOPT_CONNECTTIMEOUT, $this->settings['apiTimeout']);
        $this->browser->setRequestEngine($this->browserRequestEngine);

        try {
            return $this->browser->request($apiCallUrl);
        } catch (\Exception $e) {
            $this->systemLogger->log($e->getMessage(), LOG_WARNING);
        }
        return null;
    }

    /**
     * @param $methodName
     * @param array $arguments
     * @return Uri
     */
    protected function buildApiCallUrl(array $arguments = [])
    {
        $apiCallUrl = new Uri($this->settings['protocol'] . '://' . $this->settings['host']);
        $apiCallUrl->setPath('index.php');
        $apiCallUrl->setQuery(http_build_query(array_merge([
            'module' => 'API',
            'format' => 'json',
            'idSite' => $this->settings['idSite'],
            'token_auth' => $this->settings['token_auth'],
        ], $arguments)));
        return $apiCallUrl;
    }
}
