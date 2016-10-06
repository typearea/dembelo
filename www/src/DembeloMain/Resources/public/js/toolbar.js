var Toolbar = function () {

    var toolbarElement = document.getElementById('toolbar');

    var lastScroll = 0;
    var delta = 5;

    /**
     * Hide toolbar if user scroll down / show toolbar if user scroll up
     */
    function toggleToolbarOnScroll() {
        var scrollTop = document.scrollingElement.scrollTop;

        if (Math.abs(lastScroll - scrollTop) <= delta) {
            return;
        }

        if (scrollTop > lastScroll && scrollTop > toolbarElement.offsetHeight) {
            toolbarElement.classList.add('nav-up');
        } else {
            toolbarElement.classList.remove('nav-up');
        }

        lastScroll = scrollTop;
    }

    document.addEventListener('scroll', toggleToolbarOnScroll);

    return {};
}();