$(function($) {

  var currentPage = 1;

  // 加载招标列表
  function loadTender(page) {
    currentPage = page;
    var tbody = $('#tenderTable > tbody').html('');
    var alertBox = $('#tenderTable').parent().next('.alert');
    var pageBox = alertBox.next();
    alertBox.show();
    $.get('api/tender/get?page='+page, function(data) {
      var addContent = '';
      for (var i = 0; i < data.content.length; i++) { 
        addContent += '\
          <tr>\
            <th scope="row">'+data.content[i].tenderID+'</th>\
            <td>'+data.content[i].product+'</td>\
            <td>'+data.content[i].project+'</td>\
            <td>'+data.content[i].state+'</td>\
            <td>\
              <a href="#tenderDetailModal" data-toggle="modal" data-tenderid="'+data.content[i].tenderID+'">详细</a> |\
              <a href="#tenderProcessModal"  data-toggle="modal" data-tenderid="'+data.content[i].tenderID+'">流程</a> |\
              <a href="#tenderReviewModal"  data-toggle="modal" data-tenderid="'+data.content[i].tenderID+'">评审</a> |\
              <a href="#"  data-toggle="modal" data-tenderid="'+data.content[i].tenderID+'">定标</a>\
            </td>\
            </td>\
          </tr>\
        ';
      }
      tbody.html(addContent);
      pageDivide(pageBox, data.page, data.total, loadTender);

      alertBox.hide();
      resizePage();
    });
  }
  loadTender(1);

  // 招标详情
  $('#tenderDetailModal').on('show.bs.modal', function(event) {
    var modal = $(this);
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('tenderid');      // Extract info from data-* attributes
    var formBox = $(this).find('form').hide();
    var alertBox = $(this).find('.alert').show();

    if (id === 'new') {
      modal.find('.modal-header .modal-title').text('新建招标');
      // 清空数据
      formBox.find('input, textarea, select').val('');
      modal.find('.form-group > .btn-group').remove();
      // 获取可用请购
      $.get('api/tender/getPurchase', function(data) {
        var addContent = '<option>请选择关联请购</option>';
        for (var i = 0; i < data.content.length; i++) {
          addContent += '<option data-purchaseid="'+data.content[i].purchaseID+'" data-id="'+data.content[i].ID+'" data-project="'+data.content[i].projectName+'" data-product="'+data.content[i].product+'" data-productid="'+data.content[i].productArray[0].productID+'" data-productname="'+data.content[i].productArray[0].productName+'">'+data.content[i].purchaseName+'</option>';
        }
        $('#tenderPurchase').html(addContent);

        formBox.show();
        alertBox.hide();
      });
    } else if (id) {
      modal.find('.modal-header .modal-title').text('招标申请详情');
      $.get('api/tender/getDetail?tenderID='+id, function(data) {
        console.log(data);
        // 填入数据
        $('#tenderNum').val(data.content.tenderID);
        $('#tenderPurchaseID').val(data.content.purchaseID);
        $('#tenderPurchase').val(data.content.purchase);
        $('#tenderPurchase').html('<option>'+data.content.purchase+'</option>');
        $('#tenderProjectID').val(data.content.ID);
        $('#tenderProject').val(data.content.projectName);
        $('#tenderManager').val(data.content.manager);
        $('#tenderProduct').val(data.content.product);
        $('#tenderApplyDate').val(data.content.applyDate);
        $('#tenderTecDate').val(data.content.tecDate);
        $('#tenderPriceDate').val(data.content.priceDate);
        $('#tenderAdviceSuplier').val(data.content.adviceSuplier);
        $('#tenderAdviceSuplierAdd').val(data.content.adviceSuplierAdd);
        $('#tenderTip').val(data.content.tip);
        // 添加按钮
        modal.find('.form-group > .btn-group').remove();
        addProductBtn($('#tenderProduct'), data.content.productArray, false);      

        formBox.show();
        alertBox.hide();
      });
    } else {
      formBox.show();
      alertBox.hide();
    }
  });

  // 招标修改
  $('#tenderSubmit').on('click', function() {
    var thisBtn = $(this).button('loading');
    $.post('api/tender/edit', $('#tenderDetailForm').serialize(), function(data) {
      thisBtn.button('reset');
      if (data.code === 1202 || data.code === 1201) {
        $('#tenderDetailModal').modal('hide');
        loadTender(currentPage);
      } else {
        alert(data.message);
      }
    });

  });

  // 招标关联请购选择
  $('#tenderPurchase').on('change', function() {
    var selected = $(this).children('option:selected');
    $('#tenderProjectID').val(selected.data('id'));
    $('#tenderProject').val(selected.data('project'));
    $('#tenderPurchaseID').val(selected.data('purchaseid'));
    $('#tenderProduct').val(selected.data('product'));
    // 招标内容
    $('#tenderProduct').nextAll('.btn-group').remove();
    addProductBtn($('#tenderProduct'), [{productID:selected.data('productid'),productName:selected.data('productname')}], false);
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
