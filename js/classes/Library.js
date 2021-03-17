/**
 * Clears session data from localStorage and returns to index.html.
 */
export function LogOut () {
    localStorage.clear();
    window.location.href = "index.html";
}

/**
 * Add a user as an option to a dropdown menu.
 * @param {User} user
 * @param {JQuery} parent The element to which to add the option.
 */
export function addUserAsOption(user, parent)
{
    // Create html
    let option = $("<option></option>");

    // Set attribute and name
    option.attr("id", user.ID);
    option.text(user.Name);

    // Add to parent
    parent.append(option);
}