<?php
namespace Portachtzig\Neos\Piwik\Controller\Module;

/*
 * This script belongs to the Neos CMS package "Portachtzig.Neos.Piwik".
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
use TYPO3\Neos\Controller\Module\AbstractModuleController;

/**
 * Piwik Site Management Module Controller
 *
 * @package Portachtzig\Neos\Piwik\Controller\Module\Management
 */
class PiwikController extends AbstractModuleController
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
     * @var \Portachtzig\Neos\Piwik\Service\Reporting
     */
    protected $reportingService;

    /**
     * An edit view for the global Piwik settings and
     * Management Module for Piwik Sites through Piwik API
     *
     * @return void
     */
    public function indexAction()
    {
        try {
            // @todo persist host information to avoid calling this
            $piwikHost = array(
                'ip' => $this->reportingService->callAPI('API.getIpFromHeader'),
                'version' => $this->reportingService->callAPI('API.getPiwikVersion'),
                'sites' => $this->reportingService->callAPI('SitesManager.getAllSites'),
                'headerLogo' => $this->reportingService->callAPI('API.getHeaderLogoUrl'),
            );

            if ($piwikHost['sites'] !== null && array_key_exists('result', $piwikHost['sites']) && $piwikHost['sites']['result'] === 'error') {
                $this->addFlashMessage($piwikHost['sites']['message'], 'Piwik Error', Message::SEVERITY_ERROR);
                $this->view->assign('piwikError', true);
            } else {
                $this->view->assign('piwikHost', $piwikHost);
            }
        } catch (CurlEngineException $curlError) {
            $this->addFlashMessage($curlError->getMessage(), 'cURL error: ' . $curlError->getReferenceCode(), Message::SEVERITY_ERROR);
        }
    }

    /**
     * Update global Piwik settings
     *
     * @param array $piwik
     * @return void
     */
    public function updateAction(array $piwik)
    {
        $configurationPath = $this->packageManager->getPackage('Portachtzig.Neos.Piwik')->getConfigurationPath();
        $settings = $this->configurationSource->load($configurationPath . ConfigurationManager::CONFIGURATION_TYPE_SETTINGS);
        $piwik['host'] = preg_replace("(^https?://)", "", $piwik['host']);
        $settings = Arrays::setValueByPath($settings, 'Portachtzig.Neos.Piwik.host', $piwik['host']);
        $settings = Arrays::setValueByPath($settings, 'Portachtzig.Neos.Piwik.protocol', $piwik['protocol']);
        $settings = Arrays::setValueByPath($settings, 'Portachtzig.Neos.Piwik.token_auth', $piwik['token_auth']);
        if (array_key_exists('idSite', $piwik)) {
            $settings = Arrays::setValueByPath($settings, 'Portachtzig.Neos.Piwik.idSite', $piwik['idSite']);
        }
        $this->configurationSource->save($configurationPath . ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $settings);
        $this->configurationManager->flushConfigurationCache();
        $this->redirect('index');
    }

    /**
     * An edit view for a Piwik Site and its settings
     *
     * @param array $piwikSite Site to be edited
     * @return void
     */
    public function editSiteAction(array $piwikSite)
    {
        $this->view->assign('piwikSite', $piwikSite);
        $this->view->assign('clientIP', $this->request->getParentRequest()->getParentRequest()->getClientIpAddress());
        $this->view->assign('currentTime', new \DateTime());
    }

    /**
     * Update a Piwik Site through the Piwik API
     *
     * @param array $piwikSite Site to be updated
     * @return void
     */
    public function updateSiteAction(array $piwikSite)
    {
        $this->reportingService->callAPI('SitesManager.updateSite', $piwikSite);
        $this->redirect('index');
    }

}