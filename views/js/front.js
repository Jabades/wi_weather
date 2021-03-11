/**
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
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

window.addEventListener('load', function() {
    if (typeof wi_weather_url != 'undefined' && wi_weather_url != '') {
        fetch(wi_weather_url)
        .then(function(resp) { return resp.json() })
        .then(function(data) {
            console.log(data);
            if (wi_weather_provider == 'openweathermap.org') {
                var temp = data.main.temp;
                var hum = data.main.humidity;
                var wind = data.wind.speed;
            } else if (wi_weather_provider == 'weatherapi.com') {
                var temp = data.current.temp_c;
                var hum = data.current.humidity;
                var wind = data.current.wind_kph;                
            }
            $('#wi_weather_city').html(wi_weather_city);
            $('#wi_weather_temp').html(temp + 'ºC');            
            $('#wi_weather_humidity').html(hum + '%');
            $('#wi_weather_wind').html(wind + 'kph');
        })
        .catch(function() {
            //console.log('Weather API error!');
        });
    }
});