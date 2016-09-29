var Toolbar = function () {

    var menuElement = document.getElementById('menu-icon');
    menuElement.addEventListener('click', function () {
        Navigation.toggle();
    });

    return {};
}();