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
            'ip' => $this->reportingService->callAPI('API.getIpFromHeader'),
            'version' => $this->reportingService->callAPI('API.getMatomoVersion'),
            'site' => $this->reportingService->callAPI('SitesManager.getSiteFromId'),
            'headerLogo' => $this->reportingService->callAPI('API.getHeaderLogoUrl'),
        ];
        $this->view->assign('matomoHost', $matomoHost);
    }
}
