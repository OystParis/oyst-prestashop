/*
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't download this module on Froggy-Commerce.com, ThemeForest.net,
 * Addons.PrestaShop.com, or Oyst.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod / Oyst
 * @license GNU GENERAL PUBLIC LICENSE
 */

/**
 * Manage oneClick process
 */
class LogManagement {

    /**
     * Constructor
     * @param url
     */
    constructor(url) {
        this.url = url;
    }

    prepareEvent() {
        let object = this;
        $('select[name="logsFile"]').on('change', function () {
            if ($(this).val().length) {
                object.getLog($(this).val());
            }
        })
    }

    initBackend() {
        this.prepareEvent();
    }

    /**
     * Send request to start oneClick process
     */
    getLog(logFile) {

        let params = {
            action: "getLog",
            file: logFile
        };

        $.get(this.url, params, function(logContent) {
            $('#log')
                .text(logContent)
                .show()
            ;
            $('#logName').text(logFile);
        });
    }
}
