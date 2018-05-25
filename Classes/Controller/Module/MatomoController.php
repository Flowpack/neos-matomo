<?php
namespace Flowpack\Neos\Matomo\Controller\Module;

/*
 * This script belongs to the Neos CMS package "Flowpack.Neos.Matomo".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Neos\Controller\Module\AbstractModuleController;

/**
 * Matomo Site Management Module Controller
 *
 * @package Flowpack\Neos\Matomo\Controller\Module\Management
 */
class MatomoController extends AbstractModuleController
{
    /**
     * @Flow\Inject
     * @var \Flowpack\Neos\Matomo\Service\Reporting
     */
    protected $reportingService;

    /**
     * An edit view for the global Matomo settings and
     * Management Module for Matomo Sites through Matomo API
     *
     * @return void
     */
    public function indexAction()
    {
        $matomoHost = [
            'ip' => $this->reportingService->callAPI('API.getIpFromHeader', [], false),
            'version' => $this->reportingService->callAPI('API.getMatomoVersion', [], false),
            'headerLogo' => $this->reportingService->callAPI('API.getHeaderLogoUrl', [], false),
        ];

        if (is_array($this->settings['token_auth'])) {
            $tokens = $this->settings['token_auth'];
        } else {
            $tokens = ['*' => $this->settings['token_auth']];
        }

        $sites = [];
        if (is_array($this->settings['idSite'])) {
            foreach ($this->settings['idSite'] as $sitename => $siteId) {
                $sites[$sitename] = [
                    'id' => $siteId,
                    'matomoName' => $this->reportingService->callAPI('SitesManager.getSiteFromId', [], false, $sitename)
                ];
            }
        } else {
            $sites = ['*' => [
                'id' => $this->settings['idSite'],
                'matomoName' => $this->reportingService->callAPI('SitesManager.getSiteFromId', [], false)
            ]];
        }

        $this->view->assignMultiple([
            'matomoHost' => $matomoHost,
            'tokens' => $tokens,
            'sites' => $sites,
        ]);
    }
}
