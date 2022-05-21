<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Ps_metrics\Controller\Admin;

use PrestaShop\Module\Ps_metrics\Handler\NativeStatsHandler;
use PrestaShop\Module\Ps_metrics\Helper\ConfigHelper;
use PrestaShop\Module\Ps_metrics\Helper\PrestaShopHelper;
use PrestaShop\Module\Ps_metrics\Helper\ToolsHelper;
use PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShop\PsAccountsInstaller\Installer\Installer;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;

class MetricsController extends FrameworkBundleAdminController
{
    /**
     * @var \Ps_metrics
     */
    public $module;

    public function __construct(\Ps_metrics $module)
    {
        $this->module = $module;
    }

    /**
     * Initialize the content by adding Boostrap and loading the TPL
     *
     * @return Response
     */
    public function renderApp(): Response
    {
        $accountsService = null;
        try {
            /** @var PsAccounts $accounts */
            $accounts = $this->get('ps_accounts.facade');
            $accountsService = $accounts->getPsAccountsService();
        } catch (InstallerException $e) {
            $accountsService = false;
        }

        if (empty($accountsService)) {
            /** @var Installer $accountsInstaller */
            $accountsInstaller = $this->get('ps_accounts.installer');
            $accountsInstaller->install();
        }

        /** @var ToolsHelper $toolsHelper */
        $toolsHelper = $this->get('ps_metrics.helper.tools');

        /** @var PrestaShopHelper $prestashopHelper */
        $prestashopHelper = $this->get('ps_metrics.helper.prestashop');

        if (
            !empty($accountsService) &&
            empty($accountsService->getShopUuidV4()) &&
            $toolsHelper->getValue('settings_redirect', 0) === 0
        ) {
            $link = $prestashopHelper->getLink();

            $toolsHelper->redirectAdmin(
                $link->getAdminLink('MetricsController', true, [
                    'route' => 'metrics_page',
                    'settings_redirect' => 1,
                ]) . '#/settings'
            );
        }

        /** @var NativeStatsHandler $nativeStats */
        $nativeStats = $this->get('ps_metrics.handler.native.stats');
        $nativeStats->installIfIsOnboarded();

        $fullscreen = false;

        /** @var ConfigHelper $configHelper */
        $configHelper = $this->get('ps_metrics.helper.config');

        $psEventBusModuleVersion = '0.0.0';
        if (\Module::isInstalled('ps_eventbus')) {
            /** @var \Module $psEventBusModule */
            $psEventBusModule = \Module::getInstanceByName('ps_eventbus');
            $psEventBusModuleVersion = $psEventBusModule->version;
        }

        if ('true' === $toolsHelper->getValue('fullscreen')) {
            $fullscreen = true;
        }

        $pathMetricsApp = $configHelper->getUseLocalVueApp()
            ? $this->module->getPathUri() . '_dev/dist/js/metrics.js'
            : $configHelper->getPsMetricsCdnUrl() . 'js/metrics.js';
        $pathMetricsAssets = $configHelper->getUseLocalVueApp()
            ? $this->module->getPathUri() . '_dev/dist/css/style.css'
            : $configHelper->getPsMetricsCdnUrl() . 'css/style.css';

        $pathMetricsAppSourceMap = null;
        if (
            file_exists(
                _PS_MODULE_DIR_ .
                    $this->module->name .
                    '/_dev/dist/js/metrics.js.map'
            )
        ) {
            $pathMetricsAppSourceMap = $configHelper->getUseLocalVueApp()
                ? $this->module->getPathUri() . '_dev/dist/js/metrics.js.map'
                : $configHelper->getPsMetricsCdnUrl() . 'js/metrics.js.map';
        }

        $link = $prestashopHelper->getLink();

        return $this->render(
            '@Modules/ps_metrics/views/templates/admin/metrics.html.twig',
            [
                'layoutTitle' => $this->trans(
                    'PrestaShop Metrics',
                    'Admin.Navigation.Menu'
                ),
                'showContentHeader' => false,
                'pathMetricsApp' => $pathMetricsApp,
                'pathMetricsAppSourceMap' => $pathMetricsAppSourceMap,
                'pathMetricsAssets' => $pathMetricsAssets,
                'contextPsAccounts' => $this->module->loadPsAccountsAssets(),
                'metricsApiUrl' => $prestashopHelper->getLinkWithoutToken(
                    'MetricsResolverController',
                    'metrics_api_resolver'
                ),
                'adminToken' => $toolsHelper->getValue('_token'),
                'oAuthGoogleErrorMessage' => $toolsHelper->getValue(
                    'google_message_error'
                ),
                'fullscreen' => $fullscreen,
                'metricsModuleVersion' => $this->module->version,
                'eventBusModuleVersion' => $psEventBusModuleVersion,
                'graphqlEndpoint' => $link->getAdminLink(
                    'MetricsGraphqlController',
                    true,
                    ['route' => 'metrics_graphql']
                ),
                'isoCode' => $prestashopHelper->getLanguageIsoCode(),
                'currencyIsoCode' => $prestashopHelper->getCurrencyIsoCode(),
                'currentPage' => $toolsHelper->getValue('redirect'),
            ]
        );
    }
}
