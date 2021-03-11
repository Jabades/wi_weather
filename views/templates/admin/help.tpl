{*
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="panel">
    <h2>{l s='Help guide' mod='wi_welcome'}</h2>
    <p>{l s='Remember that you must have PrestaShop geolocalization enabled.' mod='wi_weather' tags=['<strong>']}</p>
    <ul>
        <li>{l s='Go to site [1]https://www.maxmind.com/[/1] register and download the [1]GeoLite2 City[/1] geolocalization pack.' mod='wi_weather' tags=['<strong>']}</li>
        <li>{l s='Uncompress the downloaded file and place the file [1]GeoLite2-City.mmdb[/1] it in your PrestaShop folder [1]/app/Resources/geoip/[/1]' mod='wi_weather' tags=['<strong>']}</li>
        <li>{l s='Go to [1]International > Localization[/1] and push the [1]Geolocalization[/1] tab.' mod='wi_weather' tags=['<strong>']}</li>
        <li>{l s='Enable the option [1]Geolocalization by ip[/1]' mod='wi_weather' tags=['<strong>']}</li>
        <li>{l s='Can set a predefined city for test purpose or target to show always same city weather.' mod='wi_weather' tags=['<strong>']}</li>
    </ul>
</div>