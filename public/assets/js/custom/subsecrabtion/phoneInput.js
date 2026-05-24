import intlTelInput from "intl-tel-input";
import "intl-tel-input/build/js/utils"; // Ensure utils is imported
import "intl-tel-input/build/js/i18n/ar"; // Import Arabic translations

const input = document.querySelector("#phone");

const iti = intlTelInput(input, {
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.7.0/build/js/utils.js",
    localizedCountries: {'eg': 'مصر'}, // Localized country names
    separateDialCode: true,
    initialCountry: "sa",
    nationalMode: true,
    hiddenInput: function (telInputName) {
        return {
            phone: "phone_full",
            country: "country_code"
        };
    },

    i18n: ar,

});

const handleChange = () => {
    let text;
    if (input.value) {
        if (iti.isValidNumber()) {
            text = '';
            input.classList.add('is-valid');
            input.classList.remove('is-invalid');
        } else {
            text = iti.getValidationError() === intlTelInput.utils.validationError.TOO_SHORT ? "الرقم قصير جدًا" : "رقم غير صالح - يرجى المحاولة مرة أخرى";
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
        }
    } else {
        text = "يرجى إدخال رقم صالح أدناه";
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
    }
    const output = document.querySelector("#output");
    output.innerHTML = text;
};

input.addEventListener('change', handleChange);
input.addEventListener('keyup', handleChange);
