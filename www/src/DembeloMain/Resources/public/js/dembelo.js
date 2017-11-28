(function () {
    "use strict";
    var windowElements = document.querySelectorAll('.window-opener');

    for (var i = 0; i < windowElements.length; i++) {
        windowElements[i].addEventListener('click', function (event) {
            var url = event.currentTarget.dataset.href,
                windowFeatures = [
                    'menubar=no',
                    'location=no',
                    'resizable=yes',
                    'scrollbars=no',
                    'status=no',
                    'height=328px',
                    'width=555px',
                    'top=200px',
                    'left=200px'
                ];
            window.open(url, '_blank', windowFeatures.join(','));
        });
    }
})();