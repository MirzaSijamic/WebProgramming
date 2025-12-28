/* UserService.js - Service for user authentication and profile management */
var UserService = {
  // Initialize the service with form validation
  init: function () {
    console.log('UserService.init() starting');
    
    var token = localStorage.getItem("token");
    if (token && token !== undefined) {
      // User is already logged in
      UserService.updateUserPanel();
    }

    UserService.bindLogoutButton();
  },

  // Setup login form validation (called when login form is loaded)
  setupLoginValidation: function () {
    console.log('Setting up login form validation');
    $("#loginForm").validate({
      rules: {
        loginEmail: {
          required: true,
          email: true,
        },
      },
      messages: {
        loginEmail: {
          required: "Please enter your email",
          email: "Please enter a valid email address",
        },
      },
      errorPlacement: function (error, element) {
        error.addClass("text-danger small");
        error.insertAfter(element.closest(".form-group"));
      },
      submitHandler: function (form) {
        var entity = {
          email: $(form).find('[name="loginEmail"]').val(),
          password: $(form).find('[name="loginPassword"]').val(),
        };
        UserService.login(entity);
        return false;
      },
    });
  },

  // Setup register form validation (called when register form is loaded)
  setupRegisterValidation: function () {
    console.log('Setting up register form validation');
    $("#registerForm").validate({
      rules: {
        registerUsername: {
          required: true,
          minlength: 2,
        },
        registerEmail: {
          required: true,
          email: true,
        },
        registerPassword: {
          required: true,
          minlength: 6,
        },
      },
      messages: {
        registerUsername: {
          required: "Please enter your name",
          minlength: "Name must be at least 2 characters",
        },
        registerEmail: {
          required: "Please enter your email",
          email: "Please enter a valid email address",
        },
        registerPassword: {
          required: "Please enter your password",
          minlength: "Password must be at least 6 characters",
        },
      },
      errorPlacement: function (error, element) {
        error.addClass("text-danger small");
        error.insertAfter(element.closest(".form-group"));
      },
      submitHandler: function (form) {
        console.log('registerForm submitHandler called - validation passed!');
        var entity = {
          name: $(form).find('[name="registerUsername"]').val(),
          email: $(form).find('[name="registerEmail"]').val(),
          password: $(form).find('[name="registerPassword"]').val(),
        };
        console.log('Calling UserService.register with entity:', entity);
        UserService.register(entity);
        return false;
      },
      invalidHandler: function (form, validator) {
        console.warn('Register form validation failed:', validator.errorList);
      }
    });
  },

  // Login user
  login: function (entity) {
    var backendUrl = UserService.getBackendUrl();
    console.log('UserService.login: attempting login to', backendUrl + "auth/login", 'with entity:', entity);
    $.ajax({
      url: backendUrl + "auth/login",
      type: "POST",
      data: JSON.stringify(entity),
      contentType: "application/json",
      dataType: "json",
      success: function (result) {
        console.log("Login successful:", result);
        if (result && result.data && result.data.token) {
          localStorage.setItem("token", result.data.token);
          localStorage.setItem("user", JSON.stringify(result.data));
          localStorage.setItem("role", result.data.role || "user");
          localStorage.setItem("isAdmin", result.data.role === "admin" ? "1" : "0");

          // Update UI
          UserService.updateUserPanel();
          UserService.showNotification("Logged in successfully", "success");

          // Redirect to home after 1 second
          setTimeout(function () {
            window.location.hash = "home";
          }, 1000);
        } else {
          UserService.showNotification("Login response invalid", "error");
        }
      },
      error: function (response) {
        console.error("Login error:", response.status, response.statusText, response.responseText);
        var errorMsg = "Login failed";
        try {
          var errorData = JSON.parse(response.responseText);
          errorMsg = errorData.error || errorData.message || errorMsg;
        } catch (e) {
          errorMsg = response.responseText || errorMsg;
        }
        UserService.showNotification(errorMsg, "error");
      },
    });
  },

  // Register new user
  register: function (entity) {
    var backendUrl = UserService.getBackendUrl();
    console.log('UserService.register: attempting registration to', backendUrl + "auth/register", 'with entity:', entity);
    $.ajax({
      url: backendUrl + "auth/register",
      type: "POST",
      data: JSON.stringify(entity),
      contentType: "application/json",
      dataType: "json",
      success: function (result) {
        console.log("Registration successful:", result);
        UserService.showNotification(
          "Registration successful! Please log in.",
          "success"
        );
        setTimeout(function () {
          window.location.hash = "login";
        }, 1500);
      },
      error: function (response) {
        console.error("Registration error:", response.status, response.statusText, response.responseText);
        var errorMsg = "Registration failed";
        try {
          var errorData = JSON.parse(response.responseText);
          errorMsg = errorData.error || errorData.message || errorMsg;
        } catch (e) {
          errorMsg = response.responseText || errorMsg;
        }
        UserService.showNotification(errorMsg, "error");
      },
    });
  },

  // Logout user
  logout: function () {
    localStorage.clear();
    UserService.updateUserPanel();
    window.location.hash = "home";
  },

  // Update user panel in header
  updateUserPanel: function () {
    var $panel = $(".user-panel");
    if (!$panel.length) return;

    var token = localStorage.getItem("token");
    if (!token) {
      $panel.html(
        '<a href="#login" class="login">Login</a> <a href="#register" class="register">Create an account</a>'
      );
      return;
    }

    var user = UserService.getUserFromStorage();
    var name = user.name || user.email || "User";
    var adminLink = UserService.isAdmin()
      ? ' <a href="#admin" class="admin">Admin</a>'
      : "";

    $panel.html(
      '<span class="logged-in">' +
        name +
        "</span>" +
        adminLink +
        ' <a href="#" id="logoutLink">Logout</a>'
    );

    $("#logoutLink").off("click").on("click", function (e) {
      e.preventDefault();
      UserService.logout();
    });
  },

  // Bind logout button
  bindLogoutButton: function () {
    $(document)
      .off("click", "#logoutButton")
      .on("click", "#logoutButton", function () {
        UserService.logout();
      });
  },

  // Get backend URL with fallback candidates
  getBackendUrl: function () {
    var candidates = [];
    var pathname = window.location.pathname || "/";

    try {
      var idx = pathname.indexOf("/frontend");
      if (idx !== -1) {
        candidates.push(pathname.substring(0, idx) + "/backend/");
      }
    } catch (e) {}

    try {
      var removed = pathname.replace(/\/[^\/]*$/, "");
      if (removed && removed !== pathname) {
        candidates.push(removed + "/backend/");
      }
    } catch (e) {}

    candidates.push("/WebProgramming/WebProgramming/backend/");
    candidates.push("/WebProgramming/backend/");
    candidates.push("/backend/");

    // Return the first candidate
    if (candidates.length > 0) {
      return candidates[0];
    }

    return "/backend/";
  },

  // Get user from localStorage
  getUserFromStorage: function () {
    try {
      var userStr = localStorage.getItem("user");
      return userStr ? JSON.parse(userStr) : {};
    } catch (e) {
      return {};
    }
  },

  // Check if user is admin
  isAdmin: function () {
    return localStorage.getItem("isAdmin") === "1";
  },

  // Check if user is logged in
  isLoggedIn: function () {
    return !!localStorage.getItem("token");
  },

  // Get auth token
  getToken: function () {
    return localStorage.getItem("token");
  },

  // Show notification (requires toastr or similar)
  showNotification: function (message, type) {
    // Try using Bootstrap alert if toastr is not available
    if (typeof toastr !== "undefined") {
      if (type === "success") {
        toastr.success(message);
      } else if (type === "error") {
        toastr.error(message);
      } else {
        toastr.info(message);
      }
    } else {
      // Fallback to console
      console.log(type + ":", message);
      alert(message);
    }
  },
};

// Initialize service when document is ready
$(document).ready(function () {
  if (typeof UserService !== "undefined") {
    console.log('Document ready: initializing UserService');
    UserService.init();
    console.log('UserService.init() called');
    // Verify jQuery Validate is loaded
    if (typeof $.validator !== 'undefined') {
      console.log('jQuery Validate plugin is loaded');
    } else {
      console.error('jQuery Validate plugin NOT loaded!');
    }
  } else {
    console.error('UserService is not defined');
  }
});
