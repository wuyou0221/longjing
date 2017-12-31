$(function($) {

  // 技术评审投标商
  $('#tecSuplierModalShow').on('click', function() {
    // 弹出新的model
    var fatherModal = $(this).parents('.modal');
    fatherModal.modal('hide');
    fatherModal.on('hidden.bs.modal', function() {  // 动画完成之后再弹出新的模态框
      $('#tecSuplierModal').modal('show');
      $(this).off('hidden.bs.modal');                     // 解除监听，避免后续冲突
    });
    $('#tecSuplierModal').on('hidden.bs.modal', function() {
      fatherModal.modal('show');
      $(this).off('hidden.bs.modal');
    });
  });

  // 价格评审投标商
  $('#priceSuplierModalShow').on('click', function() {
    // 弹出新的model
    var fatherModal = $(this).parents('.modal');
    fatherModal.modal('hide');
    fatherModal.on('hidden.bs.modal', function() {  // 动画完成之后再弹出新的模态框
      $('#priceSuplierModal').modal('show');
      $(this).off('hidden.bs.modal');                     // 解除监听，避免后续冲突
    });
    $('#priceSuplierModal').on('hidden.bs.modal', function() {
      fatherModal.modal('show');
      $(this).off('hidden.bs.modal');
    });
  });

});
