import User from "./classes/User.js";

$(function () {

    $('#overlay-shadow').on("click", function() {
        hideOverlay();
    });

    $('#loginlink').on("click", function() {
        hideOverlay();
    });

    $('#registerlink').on("click", function() {
        showOverlay();
    });

    loginForm();

    registerForm();
});

function hideOverlay() {
    $("#registerbox").hide();
}

function showOverlay() {
    $("#registerbox").show();
}

function loginForm() {
    $("#loginform").on("submit", function (event) {
        event.preventDefault();

        let array = $("#loginform").serializeArray();
        let obj = {};
        let errors = $("#lerrors");

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
    $("#registerform").on("submit", function (event) {
        event.preventDefault();

        let array = $("#registerform").serializeArray();
        let obj = {};
        let errors = $("#rerrors");

        // Make sure errors is empty
        errors.empty();

        for(let i=0; i < array.length; i++){
            obj[array[i].name] = array[i].value;
        }

        if(obj['rpassword'] !== obj['rpassword_check']) {
            errors.append("Passwords don't match!");
            return false;
        }

        if(obj['rpassword'].length < 6) {
            errors.append("Please use a password longer than 5 characters!");
            return false;
        }

        if(!$("input[name='data_collection']").prop('checked')) {
            errors.append("Please check the tick box to register.");
            return false;
        }

        let user = new User(null, obj['email'], obj['rusername'], obj['rpassword'], null);

        $.post("api/userCreate.php", { User : JSON.stringify(user) },
            function (data) {

                console.log(data);

                switch (data) {
                    case "2003":
                        errors.append("Name is already taken.");
                        break;
                    case "2004":
                        errors.append("Invalid email format.");
                        break;
                    case "2005":
                        errors.append("Email already taken.");
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