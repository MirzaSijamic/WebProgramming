/* UserAdminService.js - User administration service */
var UserAdminService = {
  adminTable: null,

  // Initialize admin view
  init: function() {
    var $table = $('#usersTable');
    if (!$table.length) return;

    // initialize or re-init DataTable
    if ($.fn.DataTable === undefined) {
      console.error('DataTables plugin not loaded');
      return;
    }
    if (UserAdminService.adminTable) {
      try {
        UserAdminService.adminTable.destroy();
        $table.empty();
      } catch (e) {
        /*ignore*/
      }
    }
    UserAdminService.adminTable = $table.DataTable({
      columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        { data: 'role' },
        { data: null, orderable: false, defaultContent: '' }
      ],
      pageLength: 10
    });

    UserAdminService.loadUsers();
    UserAdminService.bindDeleteButtons();
    UserAdminService.bindEditButtons();
    UserAdminService.bindSaveButton();
  },

  // Load all users
  loadUsers: function() {
    UtilityService.ajaxWithFallback({
      path: '/users',
      ajaxOpts: { method: 'GET', dataType: 'json' },
      success: function(resp) {
        var users = resp.data || resp;
        UserAdminService.adminTable.clear();
        users.forEach(function(u) {
          UserAdminService.adminTable.row.add(u);
        });
        UserAdminService.adminTable.draw();
        // add action buttons
        $('#usersTable tbody tr').each(function() {
          var $tr = $(this);
          var data = UserAdminService.adminTable.row($tr).data();
          if (!data) return;
          var actions =
            '<button class="btn btn-sm btn-primary btn-edit me-1" data-id="' +
            data.id +
            '">Update</button>' +
            '<button class="btn btn-sm btn-danger btn-delete" data-id="' +
            data.id +
            '">Delete</button>';
          $tr.find('td').last().html(actions);
        });
      },
      error: function() {
        alert('Failed to load users');
      }
    });
  },

  // Get user by ID
  getUserById: function(id, successCallback, errorCallback) {
    UtilityService.ajaxWithFallback({
      path: '/users/' + id,
      ajaxOpts: { method: 'GET', dataType: 'json' },
      success: successCallback,
      error: errorCallback
    });
  },

  // Update user
  updateUser: function(id, payload, successCallback, errorCallback) {
    UtilityService.ajaxWithFallback({
      path: '/users/' + id,
      ajaxOpts: {
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(payload)
      },
      success: successCallback,
      error: errorCallback
    });
  },

  // Delete user
  deleteUser: function(id, successCallback, errorCallback) {
    UtilityService.ajaxWithFallback({
      path: '/users/' + id,
      ajaxOpts: { method: 'DELETE' },
      success: successCallback,
      error: errorCallback
    });
  },

  // Bind delete button handler
  bindDeleteButtons: function() {
    var $table = $('#usersTable');
    $table.off('click.admin', '.btn-delete').on('click.admin', '.btn-delete', function() {
      var id = $(this).data('id');
      if (!confirm('Delete user ID ' + id + '?')) return;
      UserAdminService.deleteUser(
        id,
        function() {
          alert('Deleted');
          UserAdminService.loadUsers();
        },
        function() {
          alert('Delete failed');
        }
      );
    });
  },

  // Bind edit button handler
  bindEditButtons: function() {
    var $table = $('#usersTable');
    $table.off('click.admin', '.btn-edit').on('click.admin', '.btn-edit', function() {
      var id = $(this).data('id');
      UserAdminService.getUserById(
        id,
        function(resp) {
          var user = resp.data || resp;
          $('#editUserId').val(user.id);
          $('#editUserName').val(user.name || '');
          $('#editUserEmail').val(user.email || '');
          $('#editUserRole').val(user.role || 'user');
          $('#editUserPassword').val('');
          var modalEl = document.getElementById('editUserModal');
          var modal = new bootstrap.Modal(modalEl);
          modal.show();
        },
        function() {
          alert('Failed to fetch user');
        }
      );
    });
  },

  // Bind save button handler
  bindSaveButton: function() {
    $('#saveUserBtn')
      .off('click.admin')
      .on('click.admin', function() {
        var id = $('#editUserId').val();
        var payload = {
          name: $('#editUserName').val(),
          email: $('#editUserEmail').val(),
          role: $('#editUserRole').val()
        };
        var pwd = $('#editUserPassword').val();
        if (pwd) payload.password = pwd;
        UserAdminService.updateUser(
          id,
          payload,
          function() {
            alert('Saved');
            var modalEl = document.getElementById('editUserModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            UserAdminService.loadUsers();
          },
          function() {
            alert('Update failed');
          }
        );
      });
  }
};
