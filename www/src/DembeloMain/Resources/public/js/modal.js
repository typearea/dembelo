(function () {

    function hitchClick(e) {
        var modalPaywall = document.querySelector('#modalPaywall');
        modalPaywall.classList.toggle('modal-open');
        modalPaywall.dataset.url = e.target.dataset.url;
        e.preventDefault();
    }

    function submitPaywall(e) {
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
        if (submitter !== null) {
            submitter.addEventListener('click', submitPaywall);
        }
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
})();