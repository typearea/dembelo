$(document).ready(function() {
    $( window ).resize(function() {
        resizeImages();
    });
    resizeImages();
});

function resizeImages() {
    $('#main-page img').each(function() {
        var imageHeight = this.naturalHeight;
        var imageWidth = this.naturalWidth;
        var divHeight = parseInt($(this).closest('div').css('height'));
        var divWidth = parseInt($(this).closest('div').css('width'));
        if (imageHeight/imageWidth < divHeight/divWidth) { // Querformat
            if (imageHeight > divHeight) {
                $(this).css('height', divHeight + 'px');
                $(this).css('width', '');
            }
        } else { // Hochformat
            if (imageWidth > divWidth) {
                $(this).css('width', divWidth + 'px');
                $(this).css('height', '');
            }
        }

    });
}
