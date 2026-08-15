<script>
    var button = document.querySelector(".otp-submit");

    if (button) {
    var form = button.closest('form');

    if (!form) {
        var modalContent = button.closest('.modal-content');

        if (modalContent) {
            form = modalContent.querySelector('form');
        }

        if (!form) {
            form = document.querySelector('#reset-password-form') || document.querySelector('#two-factor-challenge-form');
        }
    }

    var inputs = form ? form.querySelectorAll(".otp-field > input") : [];
    var codeInput = form ? form.querySelector('input[name="code"]') : null;

    if (inputs.length && codeInput) {

    window.addEventListener("load", () => inputs[0].focus());
    button.setAttribute("disabled", "disabled");

    function syncCode() {
        const code = Array.from(inputs).map(function(input) {
            return String(input.value || '').replace(/\D/g, '');
        }).join('');

        codeInput.value = code;
    }

    inputs[0].addEventListener("paste", function (event) {
        event.preventDefault();

        const pastedValue = (event.clipboardData || window.clipboardData).getData(
            "text"
        );
        const otpLength = inputs.length;

        for (let i = 0; i < otpLength; i++) {
            if (i < pastedValue.length) {
                inputs[i].value = pastedValue[i];
                inputs[i].removeAttribute("disabled");

            } else {
                inputs[i].value = ""; // Clear any remaining inputs
            }
            if(i==5){
                checkInputs();
            }
        }

    });

    inputs.forEach((input, index1) => {
        input.addEventListener("input", () => {
            const currentInput = input;
            const nextInput = input.nextElementSibling;

            currentInput.value = String(currentInput.value || '').replace(/\D/g, '').slice(0, 1);

            if (
                nextInput &&
                currentInput.value !== ""
            ) {
                nextInput.removeAttribute("disabled");
                nextInput.focus();
            }

            button.classList.remove("active");
            button.setAttribute("disabled", "disabled");

            const inputsNo = inputs.length;
            syncCode();

            if (!inputs[inputsNo - 1].disabled && inputs[inputsNo - 1].value !== "") {
                button.classList.add("active");
                button.removeAttribute("disabled");
            }
        });

        input.addEventListener("keyup", (e) => {
            const currentInput = input;
            const nextInput = input.nextElementSibling;
            const prevInput = input.previousElementSibling;

            if (currentInput.value.length > 1) {
                currentInput.value = "";
                return;
            }

            if (
                nextInput &&
                currentInput.value !== ""
            ) {
                nextInput.removeAttribute("disabled");
                nextInput.focus();
            }

            if (e.key === "Backspace") {
                inputs.forEach((input, index2) => {
                    if (index1 <= index2 && prevInput) {
                        input.setAttribute("disabled", true);
                        input.value = "";
                        prevInput.focus();
                    }
                });
            }

            button.classList.remove("active");
            button.setAttribute("disabled", "disabled");


            const inputsNo = inputs.length;
            syncCode();

            if (!inputs[inputsNo - 1].disabled && inputs[inputsNo - 1].value !== "") {
                button.classList.add("active");
                button.removeAttribute("disabled");

                return;
            }

        });

    });

    function checkInputs(){
        inputs.forEach((input, index1) => {
            const currentInput = input;
            const nextInput = input.nextElementSibling;
            const prevInput = input.previousElementSibling;

            if (currentInput.value.length > 1) {
                currentInput.value = "";
                return;
            }

            if (
                nextInput &&
                currentInput.value !== ""
            ) {
                nextInput.removeAttribute("disabled");
                nextInput.focus();
            }



            button.classList.remove("active");
            button.setAttribute("disabled", "disabled");


            const inputsNo = inputs.length;
            syncCode();

            if (!inputs[inputsNo - 1].disabled && inputs[inputsNo - 1].value !== "") {
                button.classList.add("active");
                button.removeAttribute("disabled");

                return;
            }


        });
    }
    }
    }
</script>
