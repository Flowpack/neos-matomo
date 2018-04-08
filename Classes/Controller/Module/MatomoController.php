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
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Configuration\Source\YamlSource;
use Neos\Error\Messages\Message;
use Neos\Flow\Http\Client\CurlEngineException;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Utility\Arrays;
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
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @Flow\Inject
     * @var YamlSource
     */
    protected $configurationSource;

    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

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
        try {
            // @todo persist host information to avoid calling this
            $matomoHost = array(
                'ip' => $this->reportingService->callAPI('API.getIpFromHeader'),
                'version' => $this->reportingService->callAPI('API.getMatomoVersion'),
                'sites' => $this->reportingService->callAPI('SitesManager.getAllSites'),
                'headerLogo' => $this->reportingService->callAPI('API.getHeaderLogoUrl'),
            );

            if ($matomoHost['sites'] !== null && array_key_exists('result', $matomoHost['sites']) && $matomoHost['sites']['result'] === 'error') {
                $this->addFlashMessage($matomoHost['sites']['message'], 'Matomo Error', Message::SEVERITY_ERROR);
                $this->view->assign('matomoError', true);
            } else {
                $this->view->assign('matomoHost', $matomoHost);
            }
        } catch (CurlEngineException $curlError) {
            $this->addFlashMessage($curlError->getMessage(), 'cURL error: ' . $curlError->getReferenceCode(), Message::SEVERITY_ERROR);
        }
    }

    /**
     * Update global Matomo settings
     *
     * @param array $matomo
     * @return void
     */
    public function updateAction(array $matomo)
    {
        $configurationPath = $this->packageManager->getPackage('Flowpack.Neos.Matomo')->getConfigurationPath();
        $settings = $this->configurationSource->load($configurationPath . ConfigurationManager::CONFIGURATION_TYPE_SETTINGS);
        $matomo['host'] = preg_replace("(^https?://)", "", $matomo['host']);
        $settings = Arrays::setValueByPath($settings, 'Flowpack.Neos.Matomo.host', $matomo['host']);
        $settings = Arrays::setValueByPath($settings, 'Flowpack.Neos.Matomo.protocol', $matomo['protocol']);
        $settings = Arrays::setValueByPath($settings, 'Flowpack.Neos.Matomo.token_auth', $matomo['token_auth']);
        if (array_key_exists('idSite', $matomo)) {
            $settings = Arrays::setValueByPath($settings, 'Flowpack.Neos.Matomo.idSite', $matomo['idSite']);
        }
        $this->configurationSource->save($configurationPath . ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $settings);
        $this->configurationManager->flushConfigurationCache();
        $this->redirect('index');
    }

    /**
     * An edit view for a Matomo Site and its settings
     *
     * @param array $matomoSite Site to be edited
     * @return void
     */
    public function editSiteAction(array $matomoSite)
    {
        $this->view->assign('matomoSite', $matomoSite);
        $this->view->assign('clientIP', $this->request->getParentRequest()->getParentRequest()->getClientIpAddress());
        $this->view->assign('currentTime', new \DateTime());
    }

    /**
     * Update a Matomo Site through the Matomo API
     *
     * @param array $matomoSite Site to be updated
     * @return void
     */
    public function updateSiteAction(array $matomoSite)
    {
        $this->reportingService->callAPI('SitesManager.updateSite', $matomoSite);
        $this->redirect('index');
    }

}
