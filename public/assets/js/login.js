document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    if (!loginForm) return;

    const usernameInput = document.getElementById("username");
    const passwordInput = document.getElementById("password");
    const errorBox = document.getElementById("errorMessage");

    loginForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();

        // TEMP credentials (replace with backend later)
        const users = [
            {
                username: "admin",
                password: "admin123",
                role: "admin"
            },
            {
                username: "superadmin",
                password: "superadmin123",
                role: "superadmin"
            }
        ];

        const user = users.find(
            u => u.username === username && u.password === password
        );

        if (!user) {
            errorBox.textContent = "Incorrect username or password";
            errorBox.classList.add("show");

            usernameInput.style.borderColor = "#c62828";
            passwordInput.style.borderColor = "#c62828";
            return;
        }

        // SUCCESS
        errorBox.classList.remove("show");
        errorBox.textContent = "";

        // OPTIONAL: store role for later use
        localStorage.setItem("userRole", user.role);

        // ROLE-BASED CONDITIONS
        if (user.role === "superadmin") {
            window.location.href = "authentication.html";
        } else {
            window.location.href = "dashboard.html";
        }
    });

    // Clear error when typing
    [usernameInput, passwordInput].forEach(input => {
        input.addEventListener("input", () => {
            errorBox.classList.remove("show");
            errorBox.textContent = "";
            input.style.borderColor = "#8b0000";
        });
    });
});
