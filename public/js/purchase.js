$(function($) {

  var currentPage = 1;
  var fields = [
    'purchaseId',
    'purchaseCode',
    'purchaseType',
    'purchaseProjectId',
    'purchaseProjectName',
    'purchaseProjectCode',
    'purchaseProductName',
    'purchaseProductNum',
    'purchaseProductType',
    'purchaseBudget',
    'purchaseTechnologyParameter',
    'purchaseExplain',
    'purchaseTechnologyFile',
    'purchaseIsConform',
    'purchaseRejectReason',
    'purchaseRejectContent',
    'purchasePayment',
    'purchaseQuality',
    'purchaseDeadline',
    'purchaseArriveTime',
    'purchasePlace',
    'purchaseRecommend',
    'purchaseOrder',
    'purchaseOrderTime',
    'purchaseTip',
  ];

  // 加载请购列表
  function loadPurchase(page) {
    currentPage = page;
    // 定位各个元素
    var tbody = $('#purchaseTable > tbody').html('');
    var alertBox = $('#purchaseTable').parent().next('.alert');
    var pageBox = alertBox.next();
    alertBox.show();
    // 获取数据
    $.get('api/purchase/get?purchaseId='+page, function(data) {
      var addContent = '';
      $.map(data.content, function(n) {
        addContent += '\
          <tr>\
            <th scope="row">'+n.purchaseCode+'</th>\
            <td>'+n.purchaseProductName+'</td>\
            <td>'+n.purchaseProjectName+'</td>\
            <td>'+n.purchaseState+'</td>\
            <td>\
              <a href="#purchaseDetailModal" data-toggle="modal" data-purchaseid="'+n.purchaseId+'">详细</a> |\
              <a href="#purchaseProcessModal"  data-toggle="modal" data-purchaseid="'+n.purchaseId+'">审批流程</a> |\
              <a href="api/purchase/export?purchaseId='+n.purchaseId+'">导出</a>\
            </td>\
          </tr>\
        ';
      });
      // 填充数据
      tbody.html(addContent);
      // 分页
      pageDivide(pageBox, data.page, data.total, loadPurchase);

      alertBox.hide();
      resizePage();
    });
  }
  loadPurchase(1);

  // 请购详情
  $('#purchaseDetailModal').on('show.bs.modal', function(event) {
    var modal = $(this);
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('purchaseid');      // Extract info from data-* attributes
    var formBox = $(this).find('form').hide();
    var alertBox = $(this).find('.alert').show();

    if (id === 'new') {
      modal.find('.modal-header .modal-title').text('新建请购');
      // 清空数据
      formBox.find('input, textarea, select').val('');
      modal.find('.form-group > .btn-group').remove();
      // 获取可用项目
      $.get('api/purchase/getProject', function(data) {
        var addContent = '<option>请选择关联项目</option>';
        $.map(data.content, function(n) {
          addContent += '<option data-projectid="'+n.projectId+'" data-projectcode="'+n.projectCode+'">'+n.projectName+'</option>';
        });
        $('#purchaseProjectName').html(addContent);

        formBox.show();
        alertBox.hide();
      });
    } else if (id) {
      modal.find('.modal-header .modal-title').text('请购详情');
      $.get('api/purchase/getDetail?purchaseId='+id, function(data) {
        console.log(data);
        // 填入数据
        fillInput(fields, data.content, false);
        $('#purchaseProjectName').html('<option data-projectid="'+data.content.projectId+'">'+data.content.projectName+'</option>');
        // 添加按钮
        modal.find('.form-group > .btn-group').remove();
        addFileBtn($('#purchaseTechnologyFile'), data.content.purchaseTechnologyFileArray, true);

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
  $('#purchaseProjectName').on('change', function() {
    var selected = $(this).children('option:selected');
    $('#purchaseProjectId').val(selected.data('projectid'));
    $('#purchaseProjectCode').val(selected.data('projectcode'));
  });


  // 搜索供应商
  var timeout;
  $('.search-provider').on('input propertychange', function() {
    clearTimeout(timeout);   // 重复触发则取消
    var input = $(this);
    var inputBox = input.parents('li');   
    timeout = setTimeout(function(){
      $.get('api/provider/search?providerName='+input.val(), function(data) {
        var addContent = '';
        $.map(data.content, function(n) {
          addContent += '\
            <li><a class="item-single" href="#">'+n.providerName+'</a></li>\
          ';
        });
        // 插入
        inputBox.nextAll('li').remove();
        inputBox.after(addContent);
      });
    }, 500);
  });

});
