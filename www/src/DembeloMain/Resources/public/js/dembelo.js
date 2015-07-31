$(document).ready(
    function() {

        resizeMainPage()

        $(window).resize(
            function() {
                resizeImages();
                resizeMainPage()
            }
        );
        $("img").one(
            "load", function() {
                resizeImage(this);
            }
        ).each(
            function() {
                if(this.complete) { $(this).load(); }
            }
        );
    }
);

function resizeImages() {
    $('#main-page img').each(
        function() {
            resizeImage(this);

        }
    );
}

function resizeImage(imageElement) {
    var imageHeight = imageElement.naturalHeight;
    var imageWidth = imageElement.naturalWidth;
    var divHeight = parseInt($(imageElement).closest('div').css('height'));
    var divWidth = parseInt($(imageElement).closest('div').css('width'));
    if (imageHeight/imageWidth < divHeight/divWidth) { // Querformat
        if (imageHeight > divHeight) {
            $(imageElement).css('height', divHeight + 'px');
            $(imageElement).css('width', '');
        }
    } else { // Hochformat
        if (imageWidth > divWidth) {
            $(imageElement).css('width', divWidth + 'px');
            $(imageElement).css('height', '');
        }
    }
}

function resizeMainPage() {

    var el = $('#mainpagecontainer');
    var windowheight = $( window ).height();
    var eltop = el.offset().top;
    el.height(windowheight-eltop);

}
