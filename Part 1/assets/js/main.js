(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        var closeLinks = document.querySelectorAll('[data-confirm-close]');
        for (var i = 0; i < closeLinks.length; i++) {
            closeLinks[i].addEventListener('click', function (event) {
                if (!confirm('Are you sure to close this record?')) {
                    event.preventDefault();
                }
            });
        }

        var deleteLinks = document.querySelectorAll('[data-confirm-delete]');
        for (var j = 0; j < deleteLinks.length; j++) {
            deleteLinks[j].addEventListener('click', function (event) {
                if (!confirm('Are you sure to delete this record?')) {
                    event.preventDefault();
                }
            });
        }

        var toggleLinks = document.querySelectorAll('a[href*="action=toggle"], a[href*="remove_"]');
        for (var t = 0; t < toggleLinks.length; t++) {
            toggleLinks[t].addEventListener('click', function (event) {
                if (!confirm('Are you sure to continue?')) {
                    event.preventDefault();
                }
            });
        }

        var links = document.querySelectorAll('.side-menu a');
        var current = window.location.pathname;
        for (var k = 0; k < links.length; k++) {
            if (links[k].pathname === current) {
                links[k].className += ' active-menu';
            }
        }

        window.printPage = function () {
            window.print();
        };
    });
})();
