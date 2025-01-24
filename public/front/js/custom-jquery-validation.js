jQuery.validator.addMethod("lettersonly", function(value, element) {
    return this.optional(element) || /^[a-z," "]+$/i.test(value);
}, "Please enter only alphabetical characters");

$.validator.addMethod("digits", function(value, element) {
    return this.optional(element) || /^[0-9]+$/i.test(value);
}, "Please enter only digits.")