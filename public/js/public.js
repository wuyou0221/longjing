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
        ';
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

