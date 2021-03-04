import {User} from "./classes/User.js";

$(document).ready( function () {
    $("#registerform").submit(function (event) {
        event.preventDefault();

        var array = $("#registerform").serializeArray();
        var obj = {};
        var errors = $("#errors");

        errors.empty();

        for(let i=0; i < array.length; i++){
            obj[array[i].name] = array[i].value;
        }

        if(obj['password'] !== obj['password_check']) {
            errors.append("Passwords don't match!");
            return false;
        }

        if(obj['password'].length < 6) {
            errors.append("Please use a password longer than 5 characters!");
            return false;
        }

        if(!$("input[name='data_collection']").prop('checked')) {
            errors.append("Please check the tick box to register.");
            return false;
        }

        let user = new User(null, obj['email'], obj['username'], obj['password'], null);

        $.post("api/accountCreate.php", { User :JSON.stringify(user) },
            function (data) {

                console.log(data);

                switch (data) {
                    case "2003":
                        errors.append("Username is already taken.");
                        break;
                    case "2004":
                        errors.append("Invalid email format.");
                        break;
                    case "2005":
                        errors.append("Email already taken.")
                        break;
                    default:
                        data = JSON.parse(data);

                        localStorage.setItem("Account", data.Account);
                        localStorage.setItem("Session", data.Session);

                        window.location.href = "home.html";
                        break;

                }
            });
    });
});



