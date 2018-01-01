$(function($) {

  // 明细产品
  $('.modal').on('click', '.product-detail', function() {
    // 还原model数据
    var id = $(this).data('productid');
    if (id) {  // 查看已填写的内容
      $('#productModal').find('.modal-header .modal-title').text('产品详情');
      $('#addProductOver').css('display', 'none');
      $.get('api/product/get/'+id, function(data) {
        $('#productProject').val(data.ID);
        $('#productNum').val(data.productID);
        $('#productName').val(data.name);
        $('#productType').val(data.type);
        $('#productSum').val(data.sum);
        $('#productTip').val(data.tip);
      });
    } else {   // 新建
      $('#productProject').val($('#projectNum').val());
      $('#productModal').find('.modal-header .modal-title').text('添加产品');
      $('#productModal').find('input, textarea').val('');
      $('#addProductOver').css('display', 'inline-block');
    }
    // 弹出新的model
    var fatherModal = $(this).parents('.modal');
    fatherModal.modal('hide');
    fatherModal.on('hidden.bs.modal', function() {  // 动画完成之后再弹出新的模态框
      $('#productModal').modal('show');
      $('#productModal').data('father', fatherModal.attr('id'));
      $(this).off('hidden.bs.modal');                     // 解除监听，避免后续冲突
    });
    $('#productModal').on('hidden.bs.modal', function() {
      fatherModal.modal('show');
      $(this).off('hidden.bs.modal');
    });
  });

  // 添加/修改明细产品
  $('#productModal').on('click', '#addProductOver', function() {
    $(this).modal('hide');
    $.post('api/product/edit', $('#productForm').serialize(), function(data) {
      var addContent = '\
        <div class="btn-group" role="group">\
          <button type="button" class="btn btn-default product-detail" data-productid="'+data.productID+'">'+data.productName+'</button>\
          <button type="button" class="btn btn-danger del-product" data-productid="'+data.productID+'"><span class="glyphicon glyphicon-remove"></span></button>\
        </div>\
      ';
      var fatherModal = $('#'+$('#productModal').data('father'));
      fatherModal.find('.product-detail').last().before(addContent);
      var thisInput = fatherModal.find('[name=product]');
      thisInput.val(thisInput.val()+data.productID+',');
    });
  });

  // 删除明细产品
  $('form').on('click', '.del-product', function() {
    var thisItem =  $(this).parent();
    var thisInput = thisItem.prevAll('input');
    // 删除input中的数据
    thisInput.val(thisInput.val().replace(thisItem.data('productid')+',',''));
    thisItem.remove();
  });

  // 搜索物料
  $('#productModal').on('click', '.item-single', function(event) {
    $('#productName').val($(this).text());
  });

  $('.search-item').on('input propertychange', function(event) {
    console.log($(this));
  });

});
