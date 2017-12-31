$(function($) {

  // 内容适配屏幕高度
  var bodyHeight = window.innerHeight - 224;
  $('#pageBody').css('min-height', bodyHeight+'px');
  $('#slidebar').css('min-height', $('#pageBody')[0].clientHeight+'px');
  $('.login-bar').css('min-height', $('#pageBody')[0].clientHeight+'px');
  if (window.innerWidth > 768) {
    $('#slidebar').css('min-height', $('#pageBody')[0].clientHeight+'px');
  } else {
    $('#slidebar').css('min-height', '0');
  }


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


  // 项目详情
  $('#projectDetailModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id');      // Extract info from data-* attributes
    if (id) {
      $(this).find('.modal-header .modal-title').text('项目详情');
      $('#projectName').val(name);
    } else {
      $(this).find('.modal-header .modal-title').text('新建项目');
    }
  });

  // 项目修改
  $('#projectSubmit').on('click', function() {
    var thisBtn = $(this).button('loading');
    $.post('api/project/edit', $('#projectDetailForm').serialize(), function(data) {
      thisBtn.button('reset');
      if (code === 1001) {
        $('#projectDetailModal').modal('hide');
      } else {
        alert(data.message);
      }
    });

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

  // 上传附件or导入Excel
  $('.upload-file').on('click', function() {
    // 添加文件上传input
    $('body').append('<input type="file" name="file" style="display:none;">')
    var fileInput = $(':file').last();
    var thisBtn = $(this);
    var thisInput = thisBtn.prevAll('input:hidden');
    var api = '';
    // 判断上传附件或导入excel
    if (thisBtn.data('type') === 'excel') {
      api = 'excel';
      fileInput.attr('accept', 'application/vnd.ms-excel');
    } else {
      api = 'upload';
      fileInput.attr('accept', 'application/msword, application/pdf');
    }
    fileInput.click();

    // 上传
    fileInput.one('change', function() {
      // 获取数据
      var formData = new FormData();
      formData.append('file', fileInput[0].files[0]);
      // 上传数据
      if(formData){
        thisBtn.button('loading');
        $.ajax({
            url: 'api/file/'+api,  //server script to process data
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data) {
              if (data.code === 1031) {
                // 上传成功
                (thisBtn.data('type') === 'excel') ? exceled(data) : uploaded(data);
              }else {
                alert(data.message);
              }
              $(':file').remove();
              thisBtn.button('reset');
            },
            //Options to tell JQuery not to process data or worry about content-type
            cache: false,
            contentType: false,
            processData: false
        });
      }
      // 上传成功处理函数
      function uploaded(data) {
        var addContent = '\
          <div class="btn-group" role="group" data-fileid="'+data.fileID+'">\
            <a href="api/file/download/'+data.downloadUrl+'" role="button" class="btn btn-default">'+data.fileName+' | '+data.fileTime+'</a>\
            <button type="button" class="btn btn-danger del-file"><span class="glyphicon glyphicon-remove"></span></button>\
          </div>\
        '
        thisBtn.before(addContent);
        thisInput.val(thisInput.val()+data.fileID+',');
      }
      function exceled(data) {
        console.log(data);
        fileInput.removeAttr('accept');
      }
    });
  });

  // 删除附件
  $('form').on('click', '.del-file', function() {
    var thisItem =  $(this).parent();
    var thisInput = thisItem.prevAll('input');
    // 删除input中的数据
    thisInput.val(thisInput.val().replace(thisItem.data('fileid')+',',''));
    thisItem.remove();
  });

  // 明细产品
  $('.modal').on('click', '.product-detail', function() {
    // 还原model数据
    var id = $(this).data('productid');
    if (id) {  // 查看已填写的内容
      $('#productModal').find('.modal-header .modal-title').text('产品详情');
      $('#addProductOver').css('display', 'none');
      $.get('api/product/get?productID='+id, function(data) {
        $('#productNum').val(data.productID);
        $('#productName').val(data.name);
        $('#productType').val(data.type);
        $('#productSum').val(data.sum);
        $('#productTip').val(data.tip);
      });
    } else {   // 新建
      $('#productModal').find('.modal-header .modal-title').text('添加产品');
      $('#productModal').find('input').val('');
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
      '
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



  // 采购关联项目
  $('#purchaseDetailModal').on('click', '.item-single', function(event) {
    $('#purchaseProject').val($(this).text());
  });


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


