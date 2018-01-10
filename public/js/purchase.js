$(function($) {

  var currentPage = 1;

  // 加载请购列表
  function loadPurchase(page) {
    currentPage = page;
    var tbody = $('#purchaseTable > tbody').html('');
    var alertBox = $('#purchaseTable').parent().next();
    var pageBox = alertBox.next();
    alertBox.show();
    $.get('api/purchase/get/'+page, function(data) {
      var addContent = '';
      for (var i = 0; i < data.content.length; i++) { 
        addContent += '\
          <tr>\
            <th scope="row">'+data.content[i].purchaseID+'</th>\
            <td>'+data.content[i].product+'</td>\
            <td>'+data.content[i].project+'</td>\
            <td>'+data.content[i].state+'</td>\
            <td>\
              <a href="#purchaseDetailModal" data-toggle="modal" data-purchaseid="'+data.content[i].purchaseID+'">详细</a> |\
              <a href="#purchaseProcessModal"  data-toggle="modal" data-purchaseid="'+data.content[i].purchaseID+'">审批流程</a> |\
              <a href="api/purchase/export?purchaseID='+data.content[i].purchaseID+'">导出</a>\
            </td>\
          </tr>\
        ';
      }
      tbody.html(addContent);
      pageDivide(pageBox, data.page, data.total, loadPurchase);

      alertBox.hide();
      resizePage();
    });
  }
  loadPurchase(1);

  // 请购详情
  $('#purchaseDetailModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('purchaseid');      // Extract info from data-* attributes
    var formBox = $(this).find('form').hide();
    var alertBox = $(this).find('.alert').show();
    if (id === 'new') {
      $(this).find('.modal-header .modal-title').text('新建请购');
      // 清空数据
      formBox.find('input, textarea, select').val('');
      $(this).find('.form-group > .btn-group').remove();
      // 获取可用项目
      $.get('api/purchase/getProject', function(data) {
        var addContent = '<option>请选择关联项目</option>';
        for (var i = 0; i < data.content.length; i++) {
          addContent += '<option data-id="'+data.content[i].ID+'" data-code="'+data.content[i].code+'">'+data.content[i].name+'</option>';
        }
        $('#purchaseProject').html(addContent);

        formBox.show();
        alertBox.hide();
      });
    } else if (id) {
      $(this).find('.modal-header .modal-title').text('请购详情');
      $.get('api/purchase/getDetail?purchaseID='+id, function(data) {
        console.log(data);
        // 填入数据
        $('#purchaseNum').val(data.content.purchaseID);
        $('#purchaseType').val(data.content.type);
        $('#purchaseProjectID').val(data.content.ID);
        $('#purchaseProject').val(data.content.project);
        $('#purchaseProject').html('<option data-id="'+data.content.ID+'">'+data.content.project+'</option>');
        $('#purchaseProjectCode').val(data.content.code);
        $('#purchaseProduct').val(data.content.product);
        $('#purchaseDept').val(data.content.dept);
        $('#purchaseBudget').val(data.content.budget);
        $('#purchaseTecPara').val(data.content.tecPara);
        $('#purchaseExplain').val(data.content.explain);
        $('#purchaseTecFile').val(data.content.tecFile);
        $('#purchaseIsConform').val(data.content.isConform);
        $('#purchaseNotReason').val(data.content.notReason);
        $('#purchaseNotContent').val(data.content.notContent);
        $('#purchaseWay').val(data.content.way);
        $('#purchaseQuality').val(data.content.quality);
        $('#purchaseDDL').val(data.content.ddl);
        $('#purchaseArriveDate').val(data.content.arriveDate);
        $('#purchasePlace').val(data.content.place);
        $('#purchaseRecommend').val(data.content.trecommendip);
        $('#purchaseOrder').val(data.content.order);
        $('#purchaseOrderDate').val(data.content.orderDate);
        $('#purchaseTip').val(data.content.tip);
        // 添加按钮
        addFileBtn($('#purchaseTecFile'), data.content.tecFileArray, true);
        addProductBtn($('#purchaseProduct'), data.content.productArray, true);      

        formBox.show();
        alertBox.hide();
      });
    } else {
      formBox.show();
      alertBox.hide();
    }
  });

  // 请购修改
  $('#purchaseSubmit').on('click', function() {
    var thisBtn = $(this).button('loading');
    $.post('api/purchase/edit', $('#purchaseDetailForm').serialize(), function(data) {
      thisBtn.button('reset');
      if (data.code === 1152 || data.code === 1151) {
        $('#purchaseDetailModal').modal('hide');
        loadPurchase(currentPage);
      } else {
        alert(data.message);
      }
    });

  });

  // 采购关联项目选择
  $('#purchaseProject').on('change', function() {
    var selected = $(this).children('option:selected');
    $('#purchaseProjectID').val(selected.data('id'));
    $('#purchaseProjectCode').val(selected.data('code'));
    $('#purchaseProduct').val('');
    $('#purchaseProduct').nextAll('.btn-group').remove();
    $('[data-type="addfrom"]').data('once', 'false');
  });

});
