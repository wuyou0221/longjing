$(function($) {

  var currentPage = 1;
  var fileds = ['projectId',
                'projectName',
                'projectDescription',
                'projectType',
                'projectCode',
                'projectAddress',
                'projectCompactSum',
                'projectTarget',
                'projectPayment',
                'projectIntroduction',
                'projectCompact',
                'projectTechnologyDeal',
                'projectProduct',
                'projectManager',
                'projectSiteManager',
                'projectDesignManager',
                'projectPurchaseManager',
                'projectReceiver',
                'projectPlan',
                'projectPurchasePlan',
                'projectOtherFile',
                'projectTip'
               ];

  // 加载项目列表
  function loadProject(page) {
    currentPage = page;
    // 定位各个元素
    var tbody = $('#projectTable > tbody').html('');
    var alertBox = $('#projectTable').parent().next();
    var pageBox = alertBox.next('.alert');
    alertBox.show();
    // 获取数据
    $.get('api/project/get?pageId='+page, function(data) {
      var addContent = '';
      $.map(data.content, function(n) {
        addContent += '\
          <tr>\
            <td>'+n.projectName+'</td>\
            <td>'+n.projectManager+'</td>\
            <td>'+n.projectState+'</td>\
            <td>\
              <a href="#projectDetailModal" data-toggle="modal" data-projectid="'+n.projectId+'">详细</a> |\
              <a href="#projectProcessModal"  data-toggle="modal" data-projectid="'+n.projectId+'">审批流程</a> |\
              <a href="#projectProcessModalTotal"  data-toggle="modal" data-projectid="'+n.projectId+'">总流程</a>\
            </td>\
          </tr>\
        ';
      });
      // 填充数据
      tbody.html(addContent);
      // 分页
      pageDivide(pageBox, data.page, data.total, loadProject);
      
      alertBox.hide();
      resizePage();
    });
  }
  loadProject(1);

  // 项目详情
  $('#projectDetailModal').on('show.bs.modal', function(event) {
    var modal = $(this);
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('projectid');      // Extract info from data-* attributes
    var formBox = modal.find('form').hide();
    var alertBox = modal.find('.alert').show();
    if (id === 'new') {
      modal.find('.modal-header .modal-title').text('新建项目');
      // 清空数据
      modal.find('input, textarea, select').val('');
      modal.find('.form-group > .btn-group').remove();
      formBox.show();
      alertBox.hide();
    } else if (id) {
      modal.find('.modal-header .modal-title').text('项目详情');
      $.get('api/project/getDetail?projectId='+id, function(data) {
        console.log(data);
        // 填入数据
        fillInput(fileds, data.content, false);
        // 添加按钮
        modal.find('.form-group > .btn-group').remove();
        addFileBtn($('#projectCompact'), data.content.projectCompactArray, true);
        addFileBtn($('#projectTechnologyDeal'), data.content.projectTechnologyDealArray, true);
        addFileBtn($('#projectOtherFile'), data.content.projectOtherFileArray, true);
        addFileBtn($('#projectPlan'), data.content.projectPlanArray, true);
        addFileBtn($('#projectPurchasePlan'), data.content.projectPurchasePlanArray, true);
        addFileBtn($('#projectProduct'), data.content.projectProductArray, true);

        formBox.show();
        alertBox.hide();
      });
    } else {
      formBox.show();
      alertBox.hide();
    }
  });

  // 项目修改
  $('#projectSubmit').on('click', function() {
    var thisBtn = $(this).button('loading');
    $.post('api/project/edit', $('#projectDetailForm').serialize(), function(data) {
      thisBtn.button('reset');
      if (data.code === 1052 || data.code === 1051) {
        $('#projectDetailModal').modal('hide');
        loadProject(currentPage);
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
