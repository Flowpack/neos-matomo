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
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Http\Client\Browser;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Service\Controller\AbstractServiceController;


// @todo add exceptions

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
     * @todo make this protected for security !!!
     * @param $matomoMethod string the method that should be called e.g. 'SitesManager.getAllSites'
     * @param $arguments array that contains the httpRequest arguments for the apiCall
     * @return array
     */
    public function callAPI($matomoMethod, $arguments = array())
    {
        if (!empty($this->settings['host']) && !empty($this->settings['token_auth'] && !empty($this->settings['token_auth']))) {
            $params = 'method=' . $matomoMethod;
            foreach ($arguments as $key => $value) {
                if ($value != '') {
                    $params .= '&' . $key . '=' . rawurlencode($value);
                }
            }
            // @todo force https here or throw error ?
            $apiCallUrl = $this->settings['protocol'] . '://' . $this->settings['host'] . '/index.php?module=API&format=json&' . $params;
            $apiCallUrl .= '&idSite=' . $this->settings['idSite'] . '&token_auth=' . $this->settings['token_auth'];
            $this->browser->setRequestEngine($this->browserRequestEngine);
            $response = $this->browser->request($apiCallUrl);

            return json_decode($response->getContent(), TRUE);
        }
    }

    /**
     * Call the Matomo Reporting API for node specific statistics
     * @todo make this protected for security !!!
     * @param $node NodeInterface
     * @param $controllerContext ControllerContext
     * @param $arguments array
     * @return AbstractDataResult
     */
    public function getNodeStatistics($node = NULL, $controllerContext = NULL, $arguments = array())
    {
        if (!empty($this->settings['host']) && !empty($this->settings['token_auth'] && !empty($this->settings['token_auth']))) {
            $params = '';
            foreach ($arguments as $key => $value) {
                if (!empty($value) && $key != 'view' && $key != 'device' && $key != 'type') {
                    $params .= '&' . $key . '=' . rawurlencode($value);
                }
            }

            try {
                $pageUrl = urlencode($this->getLiveNodeUri($node, $controllerContext)->__toString());
            } catch (StatisticsNotAvailableException $err) {
                return null;
            }

            $apiCallUrl = $this->settings['protocol'] . '://' . $this->settings['host'] . '/index.php?module=API&format=json' . $params;
            $apiCallUrl .= '&pageUrl=' . $pageUrl;
            $apiCallUrl .= '&idSite=' . $this->settings['idSite'] . '&token_auth=' . $this->settings['token_auth'];
            $this->browser->setRequestEngine($this->browserRequestEngine);

            if ($arguments['view'] == 'TimeSeriesView') {
                $response = $this->browser->request($apiCallUrl);

                return new TimeSeriesDataResult($response);
            }
            if ($arguments['view'] == 'ColumnView') {
                $response = $this->browser->request($apiCallUrl);

                return new ColumnDataResult($response);
            }
            if ($arguments['type'] == 'device') {
                $apiCallUrl .= '&segment=pageUrl==' . $pageUrl;
                $response = $this->browser->request($apiCallUrl);

                return new DeviceDataResult($response);
            }
            if ($arguments['type'] == 'osFamilies') {
                $apiCallUrl .= '&segment=pageUrl==' . $pageUrl;
                $response = $this->browser->request($apiCallUrl);

                return new OperatingSystemDataResult($response);
            }
            if ($arguments['type'] == 'browsers') {
                $apiCallUrl .= '&segment=pageUrl==' . $pageUrl;
                $response = $this->browser->request($apiCallUrl);

                return new BrowserDataResult($response);
            }
            if ($arguments['type'] == 'outlinks') {
                $apiCallUrl .= '&segment=pageUrl==' . $pageUrl;
                $response = $this->browser->request($apiCallUrl);

                return new OutlinkDataResult($response);
            }
        }
    }

    /**
     * Resolve an URI for the given node in the live workspace (this is where analytics usually are collected)
     *
     * @param NodeInterface $node
     * @param ControllerContext $controllerContext
     * @return \Neos\Flow\Http\Uri
     * @throws StatisticsNotAvailableException If the node was not yet published and no live workspace URI can be resolved
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
        $nodeUri = new \Neos\Flow\Http\Uri($nodeUriString);

        return $nodeUri;
    }
}
