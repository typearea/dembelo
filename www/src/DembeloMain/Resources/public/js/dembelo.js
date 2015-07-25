$(document).ready(function() {
    $( window ).resize(function() {
        resizeImages();
    });
    $("img").one("load", function() {
        resizeImage(this);
    }).each(function() {
        if(this.complete) $(this).load();
    });
});

function resizeImages() {
    $('#main-page img').each(function() {
        resizeImage(this);

    });
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