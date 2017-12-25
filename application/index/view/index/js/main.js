$(function($) {

  // 内容适配屏幕高度
  var bodyHeight = window.innerHeight - 224;
  $('#pageBody').css('min-height', bodyHeight+'px');
  $('#slidebar').css('min-height', $('#pageBody')[0].clientHeight+'px');
  $('.login-bar').css('min-height', $('#pageBody')[0].clientHeight+'px');
  // if (window.innerWidth > 768) {
  //   $('#slidebar').css('min-height', $('#pageBody')[0].clientHeight+'px');
  // } else {
  //   $('#slidebar').css('min-height', '0');
  // }


  // 项目详情
  $('#projectDetailModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var name = button.data('name');      // Extract info from data-* attributes
    if (name === 'new') {
      $(this).find('.modal-header .modal-title').text('新建项目');
    } else if (name) {
      $(this).find('.modal-header .modal-title').text('项目详情 [ ' + name + ' ]');
      $('#projectName').val(name);
    }
  });

  // 审批流程
  $('#projectProcessModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var name = button.data('name');      // Extract info from data-* attributes

    $(this).find('.modal-header .modal-title').text('审批流程 [ ' + name + ' ]');
  });

  // 总流程
  $('#projectProcessModalTotal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var name = button.data('name');      // Extract info from data-* attributes

    $(this).find('.modal-header .modal-title').text('项目总流程 [ ' + name + ' ]');
  });

  // 明细产品
  $('.modal').on('click', '.product-detail', function() {
    // 还原model数据
    var name = $(this).data('name');
    if (name === 'new') {  // 新建
      $('#productModal').find('input').val('');
      $('#addProductOver').css('display', 'inline-block');
    } else if (name) {     // 查看已填写的内容
      $('#productName').val(name);
      $('#addProductOver').css('display', 'none');
    }
    // 弹出新的model
    var fatherModal = $(this).parents('.modal');
    console.log(fatherModal);
    fatherModal.modal('hide');
    fatherModal.on('hidden.bs.modal', function() {  // 动画完成之后再弹出新的模态框
      $('#productModal').modal('show');
      $(this).off('hidden.bs.modal');                     // 解除监听，避免后续冲突
    });
    $('#productModal').on('hidden.bs.modal', function() {
      fatherModal.modal('show');
      $(this).off('hidden.bs.modal');
    });
  });

  // 添加/修改明细产品
  $('#addProductOver').on('click', function() {
    $('#productModal').modal('hide');
  });

  // 删除明细产品
  $('.delProduct').on('click', function() {
    console.log('del');
  });

  // 搜索物料
  $('#productModal').on('click', '.item-single', function(event) {
    $('#productName').val($(this).text());
  });

  $('.search-item').on('input propertychange', function(event) {
    console.log($(this));
  });

  // 采购关联项目
  $('#purchaseDetailModal').on('click', '.item-single', function(event) {
    $('#purchaseProject').val($(this).text());
  });


  // 技术评审投标商
  $('#tecSuplierModalShow').on('click', function() {
    // 弹出新的model
    var fatherModal = $(this).parents('.modal');
    console.log(fatherModal);
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
    console.log(fatherModal);
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