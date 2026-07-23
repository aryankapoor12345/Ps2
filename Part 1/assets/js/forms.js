(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        var zoneSelect = document.querySelector('[data-zone-select]');
        var eicSelect = document.querySelector('[data-eic-select]');
        var leadersBox = document.getElementById('zone_leaders_display');
        var mobileInput = document.getElementById('eic_mobile');
        var departmentSelect = document.getElementById('department_id');
        var baseUrl = window.APP_BASE_URL || '/ntpc_safety_portal/';

        function requestJson(url, callback) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    try {
                        callback(xhr.status === 200 ? JSON.parse(xhr.responseText) : null);
                    } catch (e) {
                        callback(null);
                    }
                }
            };
            xhr.send();
        }

        function populateEics(eics) {
            eicSelect.innerHTML = '<option value="">--Select EIC--</option>';
            for (var i = 0; i < eics.length; i++) {
                var option = document.createElement('option');
                option.value = eics[i].id;
                option.appendChild(document.createTextNode(eics[i].full_name));
                option.setAttribute('data-mobile', eics[i].mobile || '');
                option.setAttribute('data-department-id', eics[i].department_id || '');
                eicSelect.appendChild(option);
            }
        }

        if (zoneSelect && eicSelect) {
            zoneSelect.addEventListener('change', function () {
                var zoneId = zoneSelect.value;
                if (leadersBox) {
                    leadersBox.innerHTML = '';
                }
                populateEics([]);
                if (mobileInput) {
                    mobileInput.value = '';
                }
                if (!zoneId) {
                    return;
                }

                requestJson(baseUrl + 'modules/ajax/get_zone_details.php?zone_id=' + encodeURIComponent(zoneId), function (data) {
                    if (leadersBox) {
                        leadersBox.innerHTML = data && data.success ? data.leaders_text : '';
                    }
                });

                requestJson(baseUrl + 'modules/ajax/get_eic_by_zone.php?zone_id=' + encodeURIComponent(zoneId), function (data) {
                    populateEics(data && data.success ? data.eics : []);
                });
            });

            eicSelect.addEventListener('change', function () {
                var selected = eicSelect.options[eicSelect.selectedIndex];
                if (mobileInput) {
                    mobileInput.value = selected ? (selected.getAttribute('data-mobile') || '') : '';
                }
                if (departmentSelect && selected && selected.getAttribute('data-department-id')) {
                    departmentSelect.value = selected.getAttribute('data-department-id');
                }
            });
        }

        var textareas = document.querySelectorAll('textarea[data-count]');
        for (var i = 0; i < textareas.length; i++) {
            var area = textareas[i];
            var target = document.getElementById(area.getAttribute('data-count'));
            if (!target) {
                continue;
            }
            var update = function (input, output) {
                output.innerHTML = input.value.length + ' characters';
            };
            area.addEventListener('input', function (event) {
                update(event.target, document.getElementById(event.target.getAttribute('data-count')));
            });
            update(area, target);
        }

        var files = document.querySelectorAll('input[type="file"][data-max-size]');
        for (var j = 0; j < files.length; j++) {
            files[j].addEventListener('change', function () {
                var max = parseInt(this.getAttribute('data-max-size'), 10);
                var allowed = (this.getAttribute('data-extensions') || 'pdf,jpg,jpeg,png,doc,docx').split(',');
                if (this.files.length && this.files[0].size > max) {
                    alert('Upload Document(PDF/JPG/DOC UPTO 2 MB)');
                    this.value = '';
                    return;
                }
                if (this.value) {
                    var ext = this.value.split('.').pop().toLowerCase();
                    if (allowed.indexOf(ext) === -1) {
                        alert('Allowed file formats are PDF, JPG, JPEG, PNG, DOC and DOCX.');
                        this.value = '';
                    }
                }
            });
        }
    });
})();
