<?php
declare(strict_types=1);

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
use GuzzleHttp\Psr7\Uri;
use Neos\Cache\Frontend\VariableFrontend;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Http\Client\Browser;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Service\Controller\AbstractServiceController;
use Neos\Neos\Service\LinkingService;

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
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @Flow\Inject
     * @var Browser
     */
    protected $browser;

    /**
     * @Flow\Inject
     *
     * @var VariableFrontend
     */
    protected $apiCache;

    /**
     * Call the Matomo Reporting API
     *
     * @param string $methodName is the method that should be called e.g. 'SitesManager.getAllSites'
     * @param array $arguments contains the httpRequest arguments for the apiCall
     * @param bool $useCache will return previously return data from Matomo if true
     * @param string $sitename is the optional identifier for the multi site configuration, if not set the first configured siteId and tokenAuth will be used
     * @return array|null
     */
    public function callAPI(
        string $methodName,
        array $arguments = [],
        bool $useCache = true,
        string $sitename = ''
    ): ?array {
        if (!empty($this->settings['host']) && !empty($this->settings['token_auth'] && !empty($this->settings['token_auth']))) {
            $apiCallUrl = $this->buildApiCallUrl($sitename, array_merge($arguments, ['method' => $methodName]));
            return $this->request($apiCallUrl, $useCache);
        }
        return null;
    }

    /**
     * Call the Matomo Reporting API for node specific statistics
     *
     * @param NodeInterface $node the node for which the statistics should be retrieved
     * @param ControllerContext $controllerContext needed to build a valid node uri
     * @param array $arguments contains the httpRequest arguments for the apiCall
     * @param bool $useCache will return previously return data from Matomo if true
     * @return AbstractDataResult
     */
    public function getNodeStatistics(
        NodeInterface $node = null,
        ControllerContext $controllerContext = null,
        array $arguments = [],
        $useCache = true
    ): AbstractDataResult {
        if (!empty($this->settings['host']) && !empty($this->settings['token_auth'] && !empty($this->settings['token_auth']))) {
            $contextProperties = $node->getContext()->getProperties();
            $contextProperties['workspaceName'] = 'live';

            /** @var ContentContext $liveContext */
            $liveContext = $this->contextFactory->create($contextProperties);
            $liveNode = $liveContext->getNodeByIdentifier($node->getIdentifier());

            try {
                $pageUrl = $this->getLiveNodeUri($liveNode, $controllerContext)->__toString();
            } catch (\Exception $e) {
                $this->logger->warning($e->getMessage(), \Neos\Flow\Log\Utility\LogEnvironment::fromMethodName(__METHOD__));
                return null;
            }

            if (array_key_exists('type', $arguments) && in_array($arguments['type'],
                    ['device', 'osFamilies', 'browsers', 'outlinks'])) {
                $arguments['segment'] = 'pageUrl==' . $pageUrl;
            }
            $arguments['pageUrl'] = $pageUrl;

            $apiCallUrl = $this->buildApiCallUrl($liveContext->getCurrentSite()->getNodeName(), $arguments);
            $cacheLifetime = $this->getCacheLifetimeForArguments($arguments);
            $results = $this->request($apiCallUrl, $useCache, $cacheLifetime);

            if ($results === null) {
                return null;
            }

            switch ($arguments['view']) {
                case 'TimeSeriesView':
                    return new TimeSeriesDataResult($results);
                    break;
                case 'ColumnView':
                    return new ColumnDataResult($results);
                    break;
            }
            switch ($arguments['type']) {
                case 'device':
                    return new DeviceDataResult($results);
                    break;
                case 'osFamilies':
                    return new OperatingSystemDataResult($results);
                    break;
                case 'browsers':
                    return new BrowserDataResult($results);
                    break;
                case 'outlinks':
                    return new OutlinkDataResult($results);
                    break;
            }
        }
        return null;
    }

    /**
     * Resolve an URI for the given node in the live workspace (this is where analytics usually are collected)
     *
     * @param NodeInterface $liveNode
     * @param ControllerContext $controllerContext
     * @return Uri
     * @throws StatisticsNotAvailableException If the node was not yet published and no live workspace URI can be resolved
     * @throws \Exception
     */
    protected function getLiveNodeUri(NodeInterface $liveNode, ControllerContext $controllerContext): Uri
    {
        if ($liveNode === null) {
            throw new StatisticsNotAvailableException('Matomo Statistics are only available on a published node',
                1445812693);
        }
        $nodeUriString = $this->linkingService->createNodeUri($controllerContext, $liveNode, null, 'html', true);
        $nodeUri = new Uri($nodeUriString);

        return $nodeUri;
    }

    /**
     * Send a request via curl to the api endpoint and returns the response
     *
     * @param Uri $apiCallUrl
     * @param bool $useCache
     * @param integer $cacheLifetime of this entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited lifetime.
     * @return array|null the json decoded content of the api response or null if an error occurs
     */
    protected function request(Uri $apiCallUrl, bool $useCache = true, ?int $cacheLifetime = null): ?array
    {
        $cacheIdentifier = sha1((string)$apiCallUrl);
        if ($useCache) {
            try {
                $cachedResults = $this->apiCache->get($cacheIdentifier);
                if (is_array($cachedResults)) {
                    return $cachedResults;
                }
            } catch (\Exception $e) {

                $this->logger->warning($e->getMessage(), \Neos\Flow\Log\Utility\LogEnvironment::fromMethodName(__METHOD__));
            }
        }

        $this->browserRequestEngine->setOption(CURLOPT_CONNECTTIMEOUT, $this->settings['apiTimeout']);
        $this->browser->setRequestEngine($this->browserRequestEngine);

        try {
            $response = $this->browser->request($apiCallUrl);
            if ($response !== null) {
                $results = json_decode($response->getBody()->getContents(), true);
                if ($useCache) {
                    $this->apiCache->set($cacheIdentifier, $results, [], $cacheLifetime);
                }
                return $results;
            }
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), \Neos\Flow\Log\Utility\LogEnvironment::fromMethodName(__METHOD__));
        }
        return null;
    }

    /**
     * Build api call url based on the given arguments.
     * Also filters some arguments we don't need in the request to the Matomo API.
     *
     * @param string $sitename
     * @param array $arguments
     * @return Uri
     */
    protected function buildApiCallUrl(string $sitename = '', array $arguments = []): Uri
    {
        $arguments = array_filter($arguments, function ($value, $key) {
            return !empty($value) && !in_array($key, ['view', 'device', 'type']);
        }, ARRAY_FILTER_USE_BOTH);

        $idSite = $this->settings['idSite'];
        if (is_array($idSite)) {
            if (is_string($sitename) && array_key_exists($sitename, $idSite)) {
                $idSite = $idSite[$sitename];
            } else {
                $idSite = array_values($idSite)[0];
            }
        }

        $tokenAuth = $this->settings['token_auth'];
        if (is_array($tokenAuth)) {
            if (is_string($sitename) && array_key_exists($sitename, $tokenAuth)) {
                $tokenAuth = $tokenAuth[$sitename];
            } else {
                $tokenAuth = array_values($tokenAuth)[0];
            }
        }

        $apiCallUrl = new Uri($this->settings['protocol'] . '://' . $this->settings['host']);
        return $apiCallUrl
            ->withPath($apiCallUrl->getPath() . '/index.php')
            ->withQuery(http_build_query(array_merge([
                'module' => 'API',
                'format' => 'json',
                'idSite' => $idSite,
                'token_auth' => $tokenAuth,
            ], $arguments)));
    }

    /**
     * Get the default cache lifetime based on query parameters
     *
     * @param array $arguments
     * @return integer|null
     */
    protected function getCacheLifetimeForArguments(array $arguments = []): ?int
    {
        if (array_key_exists('period', $arguments)) {
            $cacheLifetimes = $this->settings['cacheLifetimeByPeriod'];
            if (array_key_exists($arguments['period'], $cacheLifetimes)) {
                return $cacheLifetimes[$arguments['period']];
            }
        }
        return null;
    }
}
