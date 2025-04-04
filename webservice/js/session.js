$(document).ready(init);

function init() {
    registerLogin();
    registerLogout();
    registerSessionValidationTask();
}

function registerLogin() {
    const loginForm = $("form[name='loginForm']");
    if (loginForm.length === 0) {
        return;
    }

    const submitButton = loginForm.find("button[type='submit']");

    submitButton.click(() => {
        if (!loginForm[0].checkValidity()) {
            return;
        }

        const eMailAddress = loginForm.find("input[name='eMailAddress']").val();
        const password = loginForm.find("input[name='password']").val();

        $.ajax({
            url: "/endpoints/account_sessions.php",
            type: 'POST',
            data: $.param({
                "action": "signIn",
                "eMailAddress": eMailAddress,
                "password": password
            }),
            contentType: 'application/x-www-form-urlencoded',
            success: response => {
                const json = JSON.parse(response);
                const errorMsg = json.errorMsg;

                if (errorMsg) {
                    showErrorMessage(errorMsg);
                    return;
                }

                registerSessionValidationTask();
                forwardTo("/index.php");
            },
            error: error => {
                showErrorMessage(error);
                window.scrollTo(0, 0);
            }
        });
    });
}

function registerLogout() {
    const logoutForm = $("div[class*='logoutForm']");
    if (logoutForm.length === 0) {
        return;
    }

    const userId = (document.cookie.match(/^(?:.*;)?\s*(__Secure-)?userId\s*=\s*([^;]+)(?:.*)?$/)||[,,null])[2];
    if (userId === null) {
        forwardTo("/login.php");
        return;
    }

    $.ajax({
        url: "/endpoints/account_sessions.php",
        type: 'POST',
        data: $.param({
            "action": "signOut"
        }),
        contentType: 'application/x-www-form-urlencoded',
        success: response => {
            unregisterSessionValidationTask();
            forwardTo("/login.php");
        },
        error: error => {
            unregisterSessionValidationTask();
            forwardTo("/login.php");
        }
    });
}

function showErrorMessage(msg) {
    const container = $("body > div[class*='container']");

    container.find("div[class*='alert']").remove();

    if (msg) {
        container.prepend(
            $("<div/>")
                .addClass("alert alert-danger")
                .append(
                    $("<span/>")
                        .append(
                            $("<strong/>").text(dict["label_failure"])
                        )
                        .append(" " + (dict.hasOwnProperty(msg) ? dict[msg] : msg))
                )
        );
    }    
}

const SESSION_INTERVAL = 1 * 60 * 1000;
let schedule = null;

function registerSessionValidationTask() {
    unregisterSessionValidationTask();

    const userId = (document.cookie.match(/^(?:.*;)?\s*(__Secure-)?userId\s*=\s*([^;]+)(?:.*)?$/)||[,,null])[2];
    if (userId === null) {
        return;
    }

    console.log("[AUTHENTICATION] Session observation started.");
    runSessionValidationTask();
    schedule = setInterval(
        runSessionValidationTask,
        SESSION_INTERVAL
    );
}

function runSessionValidationTask() {
    $.ajax({
        url: "/endpoints/account_sessions.php",
        type: 'POST',
        data: $.param({
            "action": "signIn"
        }),
        contentType: 'application/x-www-form-urlencoded',
        success: response => {
            const json = JSON.parse(response);
            const errorMsg = json.errorMsg;

            if (errorMsg) {
                showErrorMessage(errorMsg);
                console.log("[AUTHENTICATION] Session is not valid anymore.");
                unregisterSessionValidationTask();
                setTimeout(
                    forwardTo.bind(null, "/logout.php"),
                    3 * 1000
                );
            } else {
                console.log(
                    "[AUTHENTICATION] Session is valid, has been updated and extended.\r\nRechecking in " 
                    + (SESSION_INTERVAL / (60 * 1000)) 
                    + "minute(s)..."
                );
            } 
        },
        error: error => {
            unregisterSessionValidationTask();
            forwardTo("/logout.php");
        }
    });
}

function unregisterSessionValidationTask() {
    if (schedule) {
        clearInterval(schedule);
        schedule = null;
        console.log("[AUTHENTICATION] Session observation stopped.");
    }
}

function forwardTo(url) {
    window.location.href = url;
}