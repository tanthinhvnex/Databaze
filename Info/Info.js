// Open the edit form and pre-fill current user information
function openEditForm() {
    document.getElementById("edit-form").style.display = "block";

    // Pre-fill the form with current user information
    document.getElementById("edit-email").value = document.getElementById("email").innerText;
    document.getElementById("edit-lastname").value = document.getElementById("lastname").innerText;
    document.getElementById("edit-firstname").value = document.getElementById("firstname").innerText;
    document.getElementById("edit-phone").value = document.getElementById("phone").innerText;
}

// Close the edit form
function closeEditForm() {
    document.getElementById("edit-form").style.display = "none";
}

// Save changes and update user info
function saveChanges(event) {
    event.preventDefault(); // Prevent form submission

    // Update the displayed information
    document.getElementById("email").innerText = document.getElementById("edit-email").value;
    document.getElementById("lastname").innerText = document.getElementById("edit-lastname").value;
    document.getElementById("firstname").innerText = document.getElementById("edit-firstname").value;
    document.getElementById("phone").innerText = document.getElementById("edit-phone").value;

    // Submit the form (only needed if sending data to the server)
    document.getElementById("userForm").submit();
}

// Close form when clicking outside the modal
window.onclick = function (event) {
    if (event.target == document.getElementById("edit-form")) {
        closeEditForm();
    }
};
