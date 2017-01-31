var Touch = function () {
    document.addEventListener('touchstart', handleTouchStart, false);
    document.addEventListener('touchmove', handleTouchMove, false);

    var xDown = null;
    var yDown = null;

    function handleTouchStart(evt) {
        xDown = evt.touches[0].clientX;
        yDown = evt.touches[0].clientY;
    }

    function handleTouchMove(evt) {
        if (!xDown || !yDown) {
            return;
        }

        var xUp = evt.touches[0].clientX;
        var yUp = evt.touches[0].clientY;

        var xDiff = xDown - xUp;
        var yDiff = yDown - yUp;

        if (Math.abs(xDiff) > Math.abs(yDiff)) {/*most significant*/
            if (xDiff > 0) {
                swipeLeft();
            } else {
                swipeRight();
            }
        } else {
            if (yDiff > 0) {
                swipeUp();
            } else {
                swipeDown();
            }
        }
        /* reset values */
        xDown = null;
        yDown = null;
    }

    function swipeLeft() {
        Navigation.hide();
    }

    function swipeRight() {
        Navigation.show();
    }

    function swipeUp() {

    }

    function swipeDown() {

    }

}();