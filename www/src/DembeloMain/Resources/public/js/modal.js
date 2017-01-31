(function () {

    function hitchClick(e) {
        var modalPaywall = document.querySelector('#modalPaywall');
        modalPaywall.classList.toggle('modal-open');
        modalPaywall.dataset.url = e.target.dataset.url;
        e.preventDefault();
    }

    function submitPaywall() {
        var modalPaywall = document.querySelector('#modalPaywall'),
            url = modalPaywall.dataset.url,
            httpRequest = new XMLHttpRequest();

        if (!httpRequest) {
            alert('Giving up :( Cannot create an XMLHTTP instance');
            return false;
        }
        httpRequest.onreadystatechange = function () {
            if(httpRequest.readyState === XMLHttpRequest.DONE && httpRequest.status === 200) {
                var response = JSON.parse(httpRequest.responseText);
                window.location = response.url;
            }
        };
        httpRequest.open('GET', url);
        httpRequest.send();
        e.preventDefault();
    }

    function paywall() {
        var hitchLinks = document.querySelectorAll('.hitch-link'),
            submitter = document.querySelector('.paywall-submit');
        for(var i=0;i<hitchLinks.length;i++){
            hitchLinks[i].addEventListener('click', hitchClick, false);
        }
        submitter.addEventListener('click', submitPaywall);
    }

    function closeModal(e) {
        var modalId = e.currentTarget.dataset.modal,
            modal = document.querySelector('#'+modalId);
        modal.classList.remove('modal-open');
    }

    function initCloser() {
        var modalCloser = document.querySelectorAll('.modal-closer');
        for(var i=0;i<modalCloser.length;i++){
            modalCloser[i].addEventListener('click', closeModal, false);
        }
    }

    initCloser();
    paywall();

    /**$('.hitches a').click(function () {
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
    });*/


    /**var modal = document.querySelector('.modal'),
        closeButtons = document.querySelectorAll('.close-modal');

    // set open modal behaviour
    document.querySelector('.open-modal').addEventListener('click', function () {
        modal.classList.toggle('modal-open');
    });

    // set close modal behaviour
    for (i = 0; i < closeButtons.length; ++i) {
        closeButtons[i].addEventListener('click', function () {
            modal.classList.toggle('modal-open');
        });
    }

    // close modal if clicked outside content area
    document.querySelector('.modal-inner').addEventListener('click', function () {
        modal.classList.toggle('modal-open');
    });

    // prevent modal inner from closing parent when clicked
    document.querySelector('.modal-content').addEventListener('click', function (e) {
        e.stopPropagation();
    });*/
})();