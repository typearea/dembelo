(function () {
    $(document).ready(
        function () {

            resizeMainPage();

            $(window).resize(
                function () {
                    resizeImages();
                    resizeMainPage()
                }
            );
            $("img").one(
                "load", function () {
                    resizeImage(this);
                }
            ).each(
                function () {
                    if (this.complete) {
                        $(this).load();
                    }
                }
            );

            $('.hitches a').click(function () {
                var el = $(this);
                $('#modalPaywall').find('.btn-primary').attr('data-url', el.data('url'));
                $('#modalPaywall').modal('show');
                return false;
            });

            $('#modalPaywall .btn-primary').click(function (event) {
                var button = event.target,
                    url = $(button).attr('data-url'),
                    data = {};

                $.getJSON(url, data, function (data, textStatus, jqXHR) {
                    window.location = data['url'];
                });
            });

            $('#readmetadatabutton').on('click', function () {
                if ($('#readmetadataarrow').hasClass('glyphicon-menu-down')) {
                    $('#readmetadataarrow').removeClass('glyphicon-menu-down');
                    $('#readmetadataarrow').addClass('glyphicon-menu-up');
                    $('#readmetadatatext').removeClass('hidden');
                    $(this).removeClass('pull-left');
                } else {
                    $('#readmetadataarrow').removeClass('glyphicon-menu-up');
                    $('#readmetadataarrow').addClass('glyphicon-menu-down');
                    $('#readmetadatatext').addClass('hidden');
                    $(this).addClass('pull-left');
                }

            });

        }
    );

    function resizeImages() {
        setTimeout(function () {
        $('#main-page img').each(
            function () {
                resizeImage(this);

            }
        );
        }, 200);
    }

    function resizeImage(imageElement) {
        var imageHeight = imageElement.naturalHeight,
            imageWidth = imageElement.naturalWidth,
            divHeight = parseInt($(imageElement).closest('div').css('height')),
            divWidth = parseInt($(imageElement).closest('div').css('width'));

        if (imageHeight / imageWidth < divHeight / divWidth) { // Querformat
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

        var el = $('#mainpagecontainer'),
            windowheight,
            eltop;

        if (el.offset() == undefined) {
            return;
        }

        windowheight = $(window).height();
        eltop = el.offset().top;
        el.height(windowheight - eltop);

    }
})();
