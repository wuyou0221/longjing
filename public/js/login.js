$(function($) {

  // 登录
  $('#loginForm').on('submit', function(event) {
    event.preventDefault();
    var alertBox = $(this).find('.alert');
    alertBox.show();
    $.post('api/user/login', $(this).serialize(), function(data) {
        alertBox.text(data.message);
        alertBox.removeClass('alert-warning alert-danger');
        if (data.code === 1001) {
          alertBox.addClass('alert-success');
          setTimeout('location.href="project"', 500);
        } else {
          alertBox.addClass('alert-danger');
        }
      },'json');
  });
  
});

