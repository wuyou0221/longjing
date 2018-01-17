$(function($) {

  // 内容适配屏幕高度
  var bodyHeight = window.innerHeight - 224;
  $('#pageBody').css('min-height', bodyHeight+'px');
  $('.login-bar').css('min-height', $('#pageBody').height()+'px');
  if (window.innerWidth > 768) {
    $('#slidebar').css('min-height', $('#pageBody').height()+'px');
  } else {
    $('#slidebar').css('min-height', '0');
  }

  // 获取用户信息
  $.get('api/user/getInfo', function(data) {
    var userBox = $('.user');
    userBox.find('img').attr('src', data.userHeadUrl);
    userBox.find('.label').text(data.userPost);
    userBox.find('.user-name').text(data.userName);
  });


  //下拉菜单选择
  $('.dropdown-add').on('click', '.item-single', function() {
    var relativeInput = $(this).parents('.dropdown').find('.search-result');
    relativeInput.val(relativeInput.val()+$(this).text()+'、');
  });
  $('.dropdown-change').on('click', '.item-single', function() {
    $(this).parents('.dropdown').find('.search-result').val($(this).text());
  });

});


/************* 公用方法 **************/


// 调整边侧栏高度
var adjustBox = $('#adjustBox');
function resizePage() {
  adjustBox.height(40);
  var addHeight = $('#pageBody').height() - adjustBox.prev().height() - 40;
  if (addHeight > 40) {
    adjustBox.height(addHeight);
  }
}

// 分页
function pageDivide(pageBox, page, total, getList) {
  var pageContent = pageBox.find('b').html(page+' / '+total);
  pageContent.prev().off();
  pageContent.prev().one('click', function() {
    getList(page-1);
  });
  pageContent.next().off();
  pageContent.next().one('click', function() {
    getList(page+1);
  });

  pageContent.prev().show();
  pageContent.next().show();
  if (page === 1) {
    pageContent.prev().hide();
  }
  if (page === total) {
    pageContent.next().hide();
  }
}


// input数据绑定
function fillInput(fieldArray, data, disable) {

  $.map(fieldArray, function(field) {
    var input = $('#'+field).val(data[field]);
    if (disable) {
      input.attr('disable', 'true');
    }
  });
}

// 添加文件按钮
function addFileBtn($input, array, editable) {
  var addContent = '';
  $.map(array, function(n) {
    addContent += '\
      <div class="btn-group" role="group" data-fileid="'+n.fileID+'">\
        <a href="api/file/download/'+n.downloadUrl+'" role="button" class="btn btn-default">'+n.fileName+' | '+n.fileTime+'</a>\
        <button type="button" class="btn btn-danger del-file"><span class="glyphicon glyphicon-remove"></span></button>\
      </div>\
    ';
  });
  var addBtn = $input.nextAll('[data-type="add"], [data-type="addfrom"]').before(addContent);
  if (!editable) {
    $input.nextAll('.btn-group').find('.del-file').hide();
    addBtn.nextAll().hide();
    addBtn.hide();
  }
}

// 添加产品明细按钮
function addProductBtn($input, array, editable) {
  var addContent = '';
  $.map(array, function(n) {
    addContent += '\
      <div class="btn-group" role="group" data-productid="'+n.productID+'">\
        <button type="button" class="btn btn-default product-detail">'+n.productName+'</button>\
        <button type="button" class="btn btn-danger del-product"><span class="glyphicon glyphicon-remove"></span></button>\
      </div>\
    ';
  });
  var addBtn = $input.nextAll('[data-type="add"], [data-type="addfrom"]').before(addContent);
  if (!editable) {
    $input.nextAll('.btn-group').find('.del-product').hide();
    addBtn.nextAll().hide();
    addBtn.hide();
  }
}
