$(function($) {

  // 上传附件or导入Excel
  $('.upload-file').on('click', function() {
    // 添加文件上传input
    $('body').append('<input type="file" name="file" style="display:none;">')
    var fileInput = $(':file').last();
    var thisBtn = $(this);
    var thisInput = thisBtn.prevAll('input:hidden');
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
            url: 'api/file/upload',  //server script to process data
            type: 'POST',
            data: formData,
            dataType: 'json',
            //Options to tell JQuery not to process data or worry about content-type
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(data){
          if (data.code === 1031) {
            // 上传成功
            if (thisBtn.data('type') === 'excel') {
              // excel数据导入接口
              $.post('api/product/excel', 'fileName='+data.downloadUrl, function(response) {
                thisInput.val(thisInput.val()+response.product);
                addProductBtn(thisInput, response.productArray, true);
                thisBtn.button('reset');
              });
            } else {
              thisInput.val(thisInput.val()+data.fileID+',');
              addFileBtn(thisInput, [data], true)
              thisBtn.button('reset');
            }            
          }else {
            alert(data.message);
          }
          
          $(':file').remove();
        });
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
