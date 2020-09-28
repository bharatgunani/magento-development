define([
    'jquery',
    'qTipDynamicOptions'
], function ($, qTip) {
    $.fn.qTipWrapper = function (options, el) {
        if (options.el) {
            $(el).find(options.el).qTip(options);
        } else {
            $(el).qTip(options);
        }
        return this;
    };

    return $.fn.qTipWrapper;
});
