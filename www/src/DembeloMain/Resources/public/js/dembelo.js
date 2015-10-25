/* Copyright (C) 2015 Michael Giesler
 *
 * This file is part of Dembelo.
 *
 * Dembelo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Dembelo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Dembelo. If not, see <http://www.gnu.org/licenses/>.
 */
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

        }
    );

    function resizeImages() {
        $('#main-page img').each(
            function () {
                resizeImage(this);

            }
        );
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
