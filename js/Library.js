

export function LogOut () {
    localStorage.clear();
    window.location.href = "index.html";
}

export function addUserAsOption(user, elem)
{
    let option = $("<option></option>");

    option.attr("id", user.ID);
    option.text(user.Name);

    elem.append(option);
}