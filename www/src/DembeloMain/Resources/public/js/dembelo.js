(function () {
    $(document).ready(
        function () {

            resizeMainPage();

            $(window).resize(
                function () {
                    resizeMainPage()
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

$(document).ready(function() {
    $('.col-xs-3 img').each(function() {
        if ($(this).height() > $(this).width()) {
            $(this).addClass('portrait');
        }
    });
});