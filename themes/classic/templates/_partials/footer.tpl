{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
<div class="container">
  <div class="row">
    {block name='hook_footer_before'}
      {hook h='displayFooterBefore'}
    {/block}
  </div>
  <div class="row reassurance">
    <div class="col-md-3 col-xs-6">
      <img src="/img/lea/lock.svg" alt="Paiement sécurisé">
      <span>Paiement sécurisé</span>
      <img src="/img/lea/cb.png" alt="Moyens de paiements">
    </div>
    <div class="col-md-3 col-xs-6">
      <img src="/img/lea/package.svg" alt="Paiement sécurisé">
      <span>Paiement sécurisé</span>
    </div>
    <div class="col-md-3 col-xs-6">
      <img src="/img/lea/shipping.svg" alt="Paiement sécurisé">
      <span>Expédié sous 48h à 72h</span>
    </div>
    <div class="col-md-3 col-xs-6">
      <img src="/img/lea/speech.svg" alt="Paiement sécurisé">
      <span>S.A.V. et assistance du Lundi au Vendredi de 9h à 18h</span>
    </div>
  </div>
</div>
<div class="footer-container">
  <div class="container">
    <div class="row">
      <div class="col-md-3 links wrapper">
        <div class="row">
          <div class="col-md-12 wrapper">
          <img src="/img/lea/logo.png" alt="La boutique de Léa">
            <img src="/img/lea/petales.png" alt="Pétales de Léa">
          </div>
        </div>
      </div>
      {block name='hook_footer'}
        {hook h='displayFooter'}
      {/block}
      <div class="col-md-3 links wrapper">
        <div class="row">
          <div class="col-md-12 wrapper">
            <p class="h3 hidden-sm-down">NOUS SUIVRE</p>
            <div class="title clearfix hidden-md-up" data-target="#footer_sub_menu" data-toggle="collapse">
              <span class="h3">NOUS SUIVRE</span>
              <span class="float-xs-right">
                <span class="navbar-toggler collapse-icons">
                  <i class="material-icons add">&#xE313;</i>
                  <i class="material-icons remove">&#xE316;</i>
                </span>
              </span>
            </div>
            <ul id="footer_sub_menu" class="collapse">
              sdsd
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <p class="text-sm-center copyrightsubfooter">
        © 2022 - La Boutique de Léa
        </p>
      </div>
    </div>
  </div>
</div>