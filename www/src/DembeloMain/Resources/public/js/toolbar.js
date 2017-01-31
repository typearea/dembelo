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
            Toolbar.addNavUpClass();
        } else {
            Toolbar.removeNavUpClass();
        }

        lastScroll = scrollTop;
    }

    document.addEventListener('scroll', toggleToolbarOnScroll);

    return {
        /**
         * Add css class "nav-up" to toolbar element
         */
        addNavUpClass: function () {
            toolbarElement.classList.add('nav-up');
        },
        /**
         * Remove css class "nav-up" to toolbar element
         */
        removeNavUpClass: function () {
            toolbarElement.classList.remove('nav-up');
        }
    };
}();