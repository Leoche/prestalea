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

namespace PrestaShop\Module\Ps_metrics\Handler;

use PrestaShop\Module\Ps_metrics\Repository\ConfigurationRepository;
use PrestaShop\Module\Ps_metrics\Tracker\Segment;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShopDatabaseException;
use PrestaShopException;

class NativeStatsHandler
{
    const METRICS_STATS_CONTROLLER = 'AdminMetricsController';
    const NATIVE_STATS_CONTROLLER = 'AdminStats';
    const NATIVE_STATS_CONTROLLER_COPY = 'AdminMetricsLegacyStatsController';

    /**
     * @var \Ps_metrics
     */
    private $module;

    /**
     * @var PsAccounts
     */
    private $psAccountsFacade;

    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    /**
     * @var array
     */
    private $moduleList = [
        'dashactivity',
        'dashtrends',
        'dashgoals',
        'dashproducts',
    ];

    /**
     * NativeStatsHandler constructor.
     *
     * @param \Ps_metrics $module
     * @param PsAccounts $psAccountsFacade
     * @param ConfigurationRepository $configurationRepository
     */
    public function __construct(
        \Ps_metrics $module,
        PsAccounts $psAccountsFacade,
        ConfigurationRepository $configurationRepository
    ) {
        $this->module = $module;
        $this->psAccountsFacade = $psAccountsFacade;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Run when installing the module
     *
     * @return bool
     */
    public function install(): bool
    {
        if (\Module::isInstalled('ps_accounts')) {
            $psAccountsService = $this->psAccountsFacade->getPsAccountsService();

            if ($psAccountsService->isAccountLinked()) {
                return $this->replaceLegacyMetricsController() &&
                    $this->disableNativeStatsModules();
            }
        }

        return $this->installMetricsControllerSideBySideWithNativeStats();
    }

    /**
     * Run this install on the settings controller
     * It will check if the user is onboarded for the first time and disable native stats if it is the case
     *
     * @return bool
     */
    public function installIfIsOnboarded(): bool
    {
        if (!\Module::isInstalled('ps_accounts')) {
            return false;
        }

        $psAccountsService = $this->psAccountsFacade->getPsAccountsService();

        if (!$psAccountsService->isAccountLinked()) {
            return false;
        }

        if ($this->configurationRepository->getFirstTimeOnboarded()) {
            return false;
        }

        $this->configurationRepository->saveFirstTimeOnboarded(true);

        return $this->replaceLegacyMetricsController() &&
            $this->disableNativeStatsModules();
    }

    /**
     * Active or disable native stats
     *
     * @return bool
     */
    public function toggleNativeStats(): bool
    {
        if ($this->nativeStatsIsEnabled()) {
            return $this->replaceLegacyMetricsController() &&
                $this->disableNativeStatsModules();
        }

        return $this->installMetricsControllerSideBySideWithNativeStats() &&
            $this->enableNativeStatsModules();
    }

    /**
     * Run when uninstalling the module
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        $legacyStatsTab = new \Tab(
            \Tab::getIdFromClassName(self::NATIVE_STATS_CONTROLLER)
        );

        if (!$legacyStatsTab->active) {
            $legacyStatsTab->active = true;
            $legacyStatsTab->save();
        }

        return $this->deleteAllStatsController() &&
            $this->enableNativeStatsModules();
    }

    /**
     * Replace legacy stats controller by metrics controller
     *
     * @return bool
     */
    private function replaceLegacyMetricsController(): bool
    {
        $this->deleteAllStatsController();

        $legacyStatsTab = new \Tab(
            \Tab::getIdFromClassName(self::NATIVE_STATS_CONTROLLER)
        );
        $legacyStatsTab->active = false;

        $tab = new \Tab();
        $tab->name = $legacyStatsTab->name;
        $tab->class_name = self::METRICS_STATS_CONTROLLER;
        $tab->active = true;
        $tab->module = $this->module->name;
        $tab->icon = 'assessment';
        $tab->id_parent = $legacyStatsTab->id_parent;

        return $legacyStatsTab->save() && $tab->add();
    }

    /**
     * Install metrics controller side by side with native stats controller
     *
     * @return bool
     */
    private function installMetricsControllerSideBySideWithNativeStats(): bool
    {
        $this->deleteAllStatsController();

        $legacyStatsTab = new \Tab(
            \Tab::getIdFromClassName(self::NATIVE_STATS_CONTROLLER)
        );
        $legacyStatsTab->active = true;

        $nativeStatsTab = new \Tab();
        $nativeStatsTab->name = $legacyStatsTab->name;
        $nativeStatsTab->class_name = self::NATIVE_STATS_CONTROLLER_COPY;
        $nativeStatsTab->active = true;
        $nativeStatsTab->module = $this->module->name;
        $nativeStatsTab->id_parent = (int) $legacyStatsTab->id;

        $metricsTab = new \Tab();
        $metricsTab->name = array_fill_keys(
            \Language::getIDs(false),
            $this->module->displayName
        );
        $metricsTab->class_name = self::METRICS_STATS_CONTROLLER;
        $metricsTab->active = true;
        $metricsTab->module = $this->module->name;
        $metricsTab->id_parent = (int) $legacyStatsTab->id;

        return $legacyStatsTab->save() &&
            $nativeStatsTab->add() &&
            $metricsTab->add();
    }

    /**
     * Delete all stats controller
     *
     * @return bool
     *
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    private function deleteAllStatsController(): bool
    {
        $metricsTab = new \Tab(
            \Tab::getIdFromClassName(self::METRICS_STATS_CONTROLLER)
        );
        $nativeStatsTab = new \Tab(
            \Tab::getIdFromClassName(self::NATIVE_STATS_CONTROLLER_COPY)
        );

        if ($metricsTab->id) {
            $metricsTab->delete();
        }

        if ($nativeStatsTab->id) {
            $nativeStatsTab->delete();
        }

        return true;
    }

    /**
     * Enable back dashboard modules
     *
     * @return bool
     */
    public function enableNativeStatsModules(): bool
    {
        // retrieve module list to enable
        $moduleListToEnable = $this->configurationRepository->getDashboardModulesToToggleAsArray();

        // if the module list is empty, do nothing
        if (empty($moduleListToEnable) || !is_array($moduleListToEnable)) {
            return true;
        }

        foreach ($moduleListToEnable as $moduleName) {
            $module = \Module::getInstanceByName($moduleName);
            if (false !== $module) {
                $module->enable();
            }
        }

        // now that modules has been enabled back again, reset the list from database
        $this->configurationRepository->saveDashboardModulesToToggle();

        /** @var Segment $segment */
        $segment = $this->module->getService('ps_metrics.tracker.segment');
        $segment->setMessage('[MTR] Enable native stats');
        $segment->track();

        return true;
    }

    /**
     * Disable dashboard modules
     *
     * @return bool
     */
    public function disableNativeStatsModules(): bool
    {
        // get module to disable
        $modulesToDisable = $this->getNativeStatsModulesToToggle();
        $disabledModuleList = [];

        foreach ($modulesToDisable as $moduleName => $isEnabled) {
            // only disable modules that is currently enable
            if ($isEnabled) {
                $module = \Module::getInstanceByName($moduleName);
                if (false !== $module) {
                    $module->disable();
                    array_push($disabledModuleList, $moduleName);
                }
            }
        }

        // save to database the list of module that has been disable by metrics in order to be able
        // to turn it on if needed
        $this->configurationRepository->saveDashboardModulesToToggle(
            $disabledModuleList
        );

        /** @var Segment $segment */
        $segment = $this->module->getService('ps_metrics.tracker.segment');
        $segment->setMessage('[MTR] Disable native stats');
        $segment->track();

        return true;
    }

    /**
     * Get the current state of dashboard modules
     * We presuming that modules is enabled if the disabled module list in database is empty
     *
     * @return bool
     */
    public function nativeStatsModulesIsEnabled(): bool
    {
        $modulesToToggle = $this->configurationRepository->getDashboardModulesToToggleAsArray();

        if (!is_array($modulesToToggle) && '' === $modulesToToggle) {
            return true;
        }

        return false;
    }

    /**
     * Check if native stats is enabled or disabled
     *
     * @return bool
     *
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public function nativeStatsIsEnabled(): bool
    {
        $nativeStatsTab = new \Tab(
            \Tab::getIdFromClassName(self::NATIVE_STATS_CONTROLLER)
        );

        return (bool) $nativeStatsTab->active;
    }

    /**
     * Create a list of module from the default list in order to know which modules is
     * currently enabled or disabled on the shop
     *
     * @return array
     */
    private function getNativeStatsModulesToToggle(): array
    {
        $modules = [];

        foreach ($this->moduleList as $moduleName) {
            $isModuleEnabled = \Module::isEnabled($moduleName);
            $modules[$moduleName] = $isModuleEnabled;
        }

        return $modules;
    }
}
