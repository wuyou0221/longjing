$(function($) {

  // 加载项目列表
  function loadProject() {
    var alertBox = $('#projectTable').parent().next();
    alertBox.show();
    $.get('api/project/get', function(data) {
      var addContent = '';
      for (var i = 0; i < data.content.length; i++) { 
        var addContent = '';
        addContent += '\
          <tr class="success">\
            <th scope="row">'+data.content[i].ID+'</th>\
            <td>'+data.content[i].name+'</td>\
            <td>'+data.content[i].manager+'</td>\
            <td>'+data.content[i].state+'</td>\
            <td>\
              <a href="#projectDetailModal" data-toggle="modal" data-id="'+data.content[i].ID+'">详细</a> |\
              <a href="#projectProcessModal"  data-toggle="modal" data-id="'+data.content[i].ID+'">审批流程</a> |\
              <a href="#projectProcessModalTotal"  data-toggle="modal" data-id="'+data.content[i].ID+'">总流程</a>\
            </td>\
          </tr>\
        ';
      }
      $('#projectTable > tbody').html(addContent);
      alertBox.hide();
    });
  }
  loadProject();

  // 项目详情
  $('#projectDetailModal').on('show.bs.modal', function(event) {
    var modal = $(this);
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id');      // Extract info from data-* attributes
    if (id) {
      modal.find('.modal-header .modal-title').text('项目详情');
      var formBox = modal.find('form').hide();
      var alertBox = modal.find('.alert').show();
      $.get('api/project/getDetail/'+id, function(data) {
        console.log(data);
        // 填入数据
        $('#projectNum').val(data.content.ID);
        $('#projectName').val(data.content.name);
        $('#projectNameAbbr').val(data.content.nameAbbr);
        $('#projectType').val(data.content.type);
        $('#projectCode').val(data.content.code);
        $('#projectAddress').val(data.content.address);
        $('#projectCompactSum').val(data.content.compactSum);
        $('#projectTarget').val(data.content.target);
        $('#projectPayWay').val(data.content.payWay);
        $('#projectIntroduction').val(data.content.introduction);
        $('#projectCompact').val(data.content.compact);
        $('#projectTecDeal').val(data.content.tecDeal);
        $('#projectOtherFile').val(data.content.otherFile);
        $('#projectProduct').val(data.content.product);
        $('#projectManager').val(data.content.manager);
        $('#projectSiteManager').val(data.content.manager2);
        $('#projectDesginManager').val(data.content.manager3);
        $('#projectPurchaseManager').val(data.content.manager4);
        $('#projectReceive').val(data.content.receive);
        $('#projectPlan').val(data.content.projectPlan);
        $('#projectPurchasePlan').val(data.content.purchasePlan);
        $('#projectTip').val(data.content.tip);
        // 添加按钮
        addFileBtn($('#projectCompact'), data.content.compactArray);
        addFileBtn($('#projectTecDeal'), data.content.tecDealArray);
        addFileBtn($('#projectOtherFile'), data.content.otherFileArray);
        addFileBtn($('#projectPlan'), data.content.projectPlanArray);
        addFileBtn($('#projectPurchasePlan'), data.content.purchasePlanArray);
        addProductBtn($('#projectProduct'), data.content.productArray);
        formBox.show();
        alertBox.hide();
      });
    } else {
      modal.find('.modal-header .modal-title').text('新建项目');
      // 清空数据
      modal.find('input').val('');
      modal.find('.form-group > .btn-group').remove();
    }
    // 添加文件按钮
    function addFileBtn(JQdom, array) {
      for (var i = 0; i < array.length; i++) {
        var addContent = '';
        addContent += '\
          <div class="btn-group" role="group" data-fileid="'+array[i].fileID+'">\
            <a href="api/file/download/'+array[i].downloadUrl+'" role="button" class="btn btn-default">'+array[i].fileName+' | '+array[i].fileTime+'</a>\
            <button type="button" class="btn btn-danger del-file"><span class="glyphicon glyphicon-remove"></span></button>\
          </div>\
        ';
      }
      JQdom.nextAll('.btn-group').remove();
      JQdom.after(addContent);
    }
    // 添加产品明细按钮
    function addProductBtn(JQdom, array) {
      for (var i = 0; i < array.length; i++) {
        var addContent = '';
        addContent += '\
          <div class="btn-group" role="group">\
            <button type="button" class="btn btn-default product-detail" data-productid="'+array[i].productID+'">'+array[i].productName+'</button>\
            <button type="button" class="btn btn-danger del-product" data-productid="'+array[i].productID+'"><span class="glyphicon glyphicon-remove"></span></button>\
          </div>\
        ';
      }
      JQdom.nextAll('.btn-group').remove();  
      JQdom.after(addContent);
    }
  });

  // 项目修改
  $('#projectSubmit').on('click', function() {
    var thisBtn = $(this).button('loading');
    $.post('api/project/edit', $('#projectDetailForm').serialize(), function(data) {
      thisBtn.button('reset');
      if (code === 1001) {
        $('#projectDetailModal').modal('hide');
        loadProject();
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

});
