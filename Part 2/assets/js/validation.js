(function () {
    function hasClass(el, cls) {
        return (' ' + el.className + ' ').indexOf(' ' + cls + ' ') > -1;
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!hasClass(form, 'validate-form')) {
            return;
        }

        var required = form.querySelectorAll('[data-required="1"]');
        for (var i = 0; i < required.length; i++) {
            if (!required[i].value.replace(/^\s+|\s+$/g, '')) {
                alert('Please fill all required fields.');
                required[i].focus();
                event.preventDefault();
                return false;
            }
        }

        var minFields = form.querySelectorAll('[data-min-length]');
        for (var m = 0; m < minFields.length; m++) {
            var min = parseInt(minFields[m].getAttribute('data-min-length'), 10);
            if (minFields[m].value.replace(/^\s+|\s+$/g, '').length < min) {
                alert('Observation description should be at least ' + min + ' characters.');
                minFields[m].focus();
                event.preventDefault();
                return false;
            }
        }

        var mobiles = form.querySelectorAll('[data-mobile="1"]');
        for (var j = 0; j < mobiles.length; j++) {
            if (mobiles[j].value && !/^[0-9]{10}$/.test(mobiles[j].value)) {
                alert('Please enter valid 10 digit mobile number.');
                mobiles[j].focus();
                event.preventDefault();
                return false;
            }
        }

        var uploads = form.querySelectorAll('input[type="file"][data-extensions]');
        for (var k = 0; k < uploads.length; k++) {
            if (!uploads[k].value) {
                continue;
            }
            var allowed = uploads[k].getAttribute('data-extensions').split(',');
            var ext = uploads[k].value.split('.').pop().toLowerCase();
            if (allowed.indexOf(ext) === -1) {
                alert('Allowed file formats are PDF, JPG, JPEG, PNG, DOC and DOCX.');
                uploads[k].focus();
                event.preventDefault();
                return false;
            }
            var maxSize = parseInt(uploads[k].getAttribute('data-max-size') || '0', 10);
            if (maxSize && uploads[k].files.length && uploads[k].files[0].size > maxSize) {
                alert('Upload Document(PDF/JPG/DOC UPTO 2 MB)');
                uploads[k].focus();
                event.preventDefault();
                return false;
            }
        }
    });
})();
