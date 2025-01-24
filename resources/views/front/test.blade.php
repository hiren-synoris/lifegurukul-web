<link rel="stylesheet" href="/vendor/intl-tel-input/build/css/intlTelInput.css" />

<form id="contactForm" class="form-horizontal">
    <div class="form-group">
        <label class="col-xs-3 control-label">Phone number</label>
        <div class="col-xs-5">
            <input type="tel" class="form-control" name="phoneNumber" />
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>
<script>
$(document).ready(function() {
    $('#contactForm')
        .find('[name="phoneNumber"]')
            .intlTelInput({
              utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js",
                autoPlaceholder: true,
                preferredCountries: ['fr', 'us', 'gb']
            });

    $('#contactForm')
        .formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                phoneNumber: {
                    validators: {
                        callback: {
                            message: 'The phone number is not valid',
                            callback: function(value, validator, $field) {
                                return value === '' || $field.intlTelInput('isValidNumber');
                            }
                        }
                    }
                }
            }
        })
        // Revalidate the number when changing the country
        .on('click', '.country-list', function() {
            $('#contactForm').formValidation('revalidateField', 'phoneNumber');
        });
});
</script>