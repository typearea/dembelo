(function () {
    "use strict";

    var ratio;

    function getAvailableSpace() {
        var toolbar = document.querySelector('header#toolbar'),
            container = document.querySelector(".genres"),
            size = {
                width: container.clientWidth-17,
                height: (window.innerHeight - toolbar.offsetHeight)
            };

        if (size.height < 275) {
            size.height = 275;
        }

        return size;
    }

    function getCoverImages() {
        return document.querySelectorAll(".cover img.coverImg");
    }

    function calculateCoversPerRow(availableSpace, coverImages) {
        return 4;
    }

    function getRatio(coverDimension) {
        if (ratio === undefined) {
            ratio = coverDimension.width/coverDimension.height;
        }

        return ratio;
    }

    function calculateNewSize(availableSpace, covers, coversPerRow) {
        var maxWidth = availableSpace.width/coversPerRow,
            maxHeight = availableSpace.height/(8/coversPerRow),
            coverDimension = {
                width: covers[0].offsetWidth,
                height: covers[0].offsetHeight
            },
            newSize = {},
            ratio = getRatio(coverDimension);

        if (maxWidth/maxHeight > coverDimension.width/coverDimension.height) {
            newSize.height = maxHeight;
            newSize.width = maxHeight*ratio;
        } else {
            newSize.width = maxWidth;
            newSize.height = maxWidth/ratio;
        }

        return newSize;
    }

    function setNewSizes(coverImages, newSize) {
        var cover, i = 0;
        for (; i < coverImages.length; ++i) {
            cover = coverImages[i];
            cover.style.width = newSize.width+"px";
            cover.style.height = newSize.height+"px";
        }
    }

    function addNewLines(coversPerRow) {
        var coverElements = document.querySelectorAll(".cover"),
            i = 0,
            cover;

        for (; i < coverElements.length; ++i) {
            cover = coverElements[i];
            if ((i)%coversPerRow === 0) {
                cover.classList.add("linebreakafter");
            } else {
                cover.classList.remove("linebreakafter");
            }
        }
    }

    function relocateFavoriteLabels(newSize) {
        var favoriteElements = document.querySelectorAll("img.favorite"),
            i = 0,
            favoriteElement;

        for (; i < favoriteElements.length; ++i) {
            favoriteElement = favoriteElements[i];
            favoriteElement.style.width = (newSize.width*0.15) + 'px';
            favoriteElement.style.marginLeft = (newSize.width*0.75)+'px';
        }
    }

    function update() {
        var availableSpace = getAvailableSpace(),
            coverImages = getCoverImages(),
            coversPerRow = calculateCoversPerRow(availableSpace, coverImages),
            newSize = calculateNewSize(availableSpace, coverImages, coversPerRow);

        addNewLines(coversPerRow);
        setNewSizes(coverImages, newSize);
        relocateFavoriteLabels(newSize);
    }

    function firstShow() {
        update();
    }

    function resize() {
        setTimeout(update, 50);
    }

    window.onresize = resize;
    document.addEventListener("DOMContentLoaded", firstShow);
    window.addEventListener("load", update);
})();
