var Navigation = function () {
    var isVisible = false;
    var navigationElement = document.getElementById('navigation');
    var toggleElement = document.getElementById('menu-icon');

    toggleElement.addEventListener('click', function () {
        Navigation.toggle();
    });

    document.addEventListener('click', function (evt) {
        if (isVisible && !navigationElement.contains(evt.target) && !toggleElement.contains(evt.target)) {
            Navigation.hide();
        }
    });

    return {
        show: function () {
            isVisible = true;
            document.body.classList.add('nav-active');
            navigationElement.classList.add('show');
        },
        hide: function () {
            isVisible = false;
            document.body.classList.remove('nav-active');
            navigationElement.classList.remove('show');
        },
        toggle: function () {
            if (!isVisible) {
                Navigation.show();
                return;
            }
            Navigation.hide();
        }
    };
}();