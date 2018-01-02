$(function($) {

  var productModal = $('#productModal');
  // 明细产品详情
  $('.modal').on('click', '.product-detail', function() {
    // 还原model数据
    var id = $(this).parent().data('productid');
    if ($(this).data('type') === 'add') {
      // 新建
      $('#productProject').val($('#projectNum').val());
      productModal.find('.modal-header .modal-title').text('添加产品');
      productModal.find('input, textarea').val('');
      $('#addProductOver').css('display', 'inline-block');
    } else if (id) {
      // 查看已填写的内容
      productModal.find('.modal-header .modal-title').text('产品详情');
      productModal.find('.alert').show();
      $('#productForm').hide();
      $('#addProductOver').css('display', 'none');
      $.get('api/product/getDetail?productID='+id, function(data) {
        $('#productNum').val(data.productID);
        $('#productName').val(data.name);
        $('#productType').val(data.type);
        $('#productSum').val(data.sum);
        $('#productTip').val(data.tip);
      })
      .done(function() {
        productModal.find('.alert').hide();
        $('#productForm').show();
      });
    }
    // 弹出新的model
    var fatherModal = $(this).parents('.modal');
    fatherModal.modal('hide');
    fatherModal.on('hidden.bs.modal', function() {  
      // 动画完成之后再弹出新的模态框
      productModal.modal('show');
      productModal.data('father', fatherModal.attr('id'));
      // 解除监听，避免后续冲突
      $(this).off('hidden.bs.modal');         
    });
    productModal.on('hidden.bs.modal', function() {
      fatherModal.modal('show');
      $(this).off('hidden.bs.modal');
    });
  });

  // 添加/修改明细产品
  productModal.on('click', '#addProductOver', function() {
    productModal.modal('hide');
    $.post('api/product/edit', $('#productForm').serialize(), function(data) {
      var fatherModal = $('#'+productModal.data('father'));
      var thisInput = fatherModal.find('[name=product]');
      thisInput.val(thisInput.val()+data.productID+',');
      addProductBtn(thisInput, [data], true);
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
  productModal.on('click', '.item-single', function(event) {
    $('#productName').val($(this).text());
    $('#itemID').val($(this).data('itemid'));
  });
  var timeout;
  $('.search-item').on('input propertychange', function() {
    clearTimeout(timeout);   // 重复触发则取消
    var input = $(this);
    var inputBox = input.parents('li');   
    timeout = setTimeout(function(){
      $.get('api/item/search?name='+input.val(), function(data) {
        var addContent = '';
        for (var i = 0; i < data.content.length; i++) {
          addContent += '\
            <li><a class="item-single" href="#" data-itemid="'+data.content[i].itemID+'">'+data.content[i].name+'</a></li>\
          ';
        }
        inputBox.nextAll('li').remove();
        inputBox.after(addContent);
      });
    }, 500);
  });

});
