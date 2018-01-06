$(function($) {

  var currentPage = 1;

  // 加载供应商列表
  function loadSuplier(page) {
    currentPage = page;
    var tbody = $('#suplierTable > tbody').html('');
    var alertBox = $('#suplierTable').parent().next();
    var pageBox = alertBox.next();
    alertBox.show();
    $.get('api/provider/get/'+page, function(data) {
      var addContent = '';
      for (var i = 0; i < data.content.length; i++) { 
        addContent += '\
          <tr>\
            <th scope="row">'+data.content[i].suplierID+'</th>\
            <td>'+data.content[i].name+'</td>\
            <td>'+data.content[i].ctName+'</td>\
            <td>'+data.content[i].ctPhone+'</td>\
            <td>\
              <a href="#suplierDetailModal" data-toggle="modal" data-suplierid="'+data.content[i].suplierID+'">详细</a> |\
              <a href="#suplierRecordModal"  data-toggle="modal" data-suplierid="'+data.content[i].suplierID+'">投标记录</a>\
            </td>\
          </tr>\
        ';
      }
      tbody.html(addContent);
      pageDivide(pageBox, data.page, data.total, loadSuplier);

      alertBox.hide();
      resizePage();
    });
  }
  loadSuplier(1);

  // 供方详情
  $('#suplierDetailModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('suplierid');      // Extract info from data-* attributes
    var formBox = $(this).find('form').hide();
    var alertBox = $(this).find('.alert').show();
    if (id === 'new') {
      $(this).find('.modal-header .modal-title').text('新建供方信息');
      // 清空数据
      formBox.find('input, textarea, select').val('');
      formBox.show();
      alertBox.hide();
    } else if (id) {
      $(this).find('.modal-header .modal-title').text('供方详情');
      $.get('api/provider/getDetail?suplierID='+id, function(data) {
        console.log(data);
        // 填入数据
        $('#suplierNum').val(data.content.suplierID);
        $('#suplierName').val(data.content.name);
        $('#suplierCode').val(data.content.code);
        $('#suplierType').val(data.content.type);
        $('#suplierCtName').val(data.content.ctName);
        $('#suplierCtPhone').val(data.content.ctPhone);
        $('#suplierCtJob').val(data.content.ctJob);
        $('#suplierCtFax').val(data.content.ctFax);
        $('#suplierEmail').val(data.content.email);
        $('#suplierHomepage').val(data.content.homepage);
        $('#suplierContact').val(data.content.contact);
        $('#suplierLegal').val(data.content.legal);
        $('#suplierFund').val(data.content.fund);
        $('#suplierQualified').val(data.content.qualified);
        $('#suplierAppraise').val(data.content.appraise);
        $('#suplierArchiveID').val(data.content.archiveID);
        $('#suplierPlace').val(data.content.place);
        $('#suplierAddress').val(data.content.address);
        $('#suplierIntroduction').val(data.content.introduction);
        $('#suplierMainProduct').val(data.content.mainProduct);
        $('#suplierFinance').val(data.content.finance);
        $('#suplierAchievement').val(data.content.achievement);
        $('#suplierTip').val(data.content.tip);

        formBox.show();
        alertBox.hide();
      });
    } else {
      formBox.show();
      alertBox.hide();
    }
  });

  // 供方修改
  $('#suplierSubmit').on('click', function() {
    var thisBtn = $(this).button('loading');
    $.post('api/provider/edit', $('#suplierDetailForm').serialize(), function(data) {
      thisBtn.button('reset');
      if (data.code === 1162 || data.code === 1161) {
        $('#suplierDetailModal').modal('hide');
        loadSuplier(currentPage);
      } else {
        alert(data.message);
      }
    });

  });

});
