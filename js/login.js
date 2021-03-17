import User from "./classes/User.js";

$(function () {
    loginForm();

    registerForm();
});

function loginForm() {
    $("#login-form").on("submit", function (event) {
        event.preventDefault();

        let array = $("#login-form").serializeArray();
        let obj = {};
        let errors = $("#login-errors");

        // Make sure errors is empty
        errors.empty();

        for(let i=0; i < array.length; i++){
            obj[array[i].name] = array[i].value;
        }

        let Username = obj['username'];
        let Password = obj['password'];

        if (Password.length < 6) {
            errors.append("Password is too short!");
            return false;
        }

        $.post("api/userLogin.php", { Username : Username, Password : Password },
            function (data) {

                switch (data) {
                    case "2000":
                        errors.append("No user");
                        break;
                    case "2001":
                        errors.append("Incorrect password");
                        break;
                    default:
                        data = JSON.parse(data);

                        localStorage.setItem("User", JSON.stringify(data.User));
                        localStorage.setItem("Session", JSON.stringify(data.Session));

                        window.location.href = "home.html";
                        break;
                }
            });
    });
}

function registerForm() {
    $("#register-form").on("submit", function (event) {
        event.preventDefault();

        let array = $("#register-form").serializeArray();
        let obj = {};
        let errors = $("#register-errors");

        // Make sure errors is empty
        errors.empty();

        for(let i=0; i < array.length; i++){
            obj[array[i].name] = array[i].value;
        }

        if(obj['r-pasword'].length < 6 || obj['r-pasword'].length > 128) {
            errors.append("Please use a password between 6 and 128 characters!");
            return false;
        }

        if(obj['r-password'] !== obj['r-pasword_check']) {
            errors.append("Passwords don't match!");
            return false;
        }

        let user = new User(null, obj['email'], obj['rusername'], obj['r-pasword'], null);

        $.post("api/userCreate.php", { User : JSON.stringify(user) },
            function (data) {
                switch (data) {
                    case "2003":
                        errors.append("Name is already taken.");
                        break;
                    case "2004":
                        errors.append("Invalid email format.");
                        break;
                    case "2005":
                        errors.append("Email is already taken.");
                        break;
                    default:
                        data = JSON.parse(data);

                        localStorage.setItem("User", JSON.stringify(data.User));
                        localStorage.setItem("Session", JSON.stringify(data.Session));

                        window.location.href = "home.html";
                        break;
                }
            });
    });
}