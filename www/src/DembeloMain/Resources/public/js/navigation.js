var Navigation = function () {
    var isVisible = false,
        navigationElement = document.getElementById('navigation'),
        toggleElement = document.getElementById('menu-icon');

    toggleElement.addEventListener('click', function () {
        Navigation.toggle();
    });

    document.addEventListener('click', function (evt) {
        if (isVisible && !navigationElement.contains(evt.target) && !toggleElement.contains(evt.target)) {
            Navigation.hide();
        }
    });

    return {
        /**
         * Show navigation element
         */
        show: function () {
            isVisible = true;
            document.body.classList.add('nav-active');
            navigationElement.classList.add('show');
            Toolbar.removeNavUpClass();
        },
        /**
         * Hide navigation element
         */
        hide: function () {
            isVisible = false;
            document.body.classList.remove('nav-active');
            navigationElement.classList.remove('show');
        },
        /**
         * Toggle navigation element
         */
        toggle: function () {
            if (!isVisible) {
                Navigation.show();
                return;
            }
            Navigation.hide();
        }
    };
}();