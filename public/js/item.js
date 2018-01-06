$(function($) {

  // 物料模态框
  $('#itemModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('itemid');      // Extract info from data-* attributes
    var parentRank = parseInt(button.data('rank')) - 1;
    // 若分类已到顶层，则特别处理
    if (parentRank < 2) {
      $('#itemParentID').val('0');
    } else {
      var parentButton = $('#rank'+parentRank+' .active').find('button');
      $('#itemParentID').val(parentButton.data('itemid'));
    }
    $('#itemRank').val(button.data('rank'));
    // 操作表单
    if (id === 'new') {
      $(this).find('.modal-header .modal-title').text('添加物料');
      $('#itemID').val('');
      $('#itemName').val('');
      $('#itemSubmit').text('添加')
      $('#itemDel').hide();
    } else if (id) {
      $(this).find('.modal-header .modal-title').text('编辑物料');
      $('#itemID').val(button.data('itemid'));
      $('#itemName').val(button.parents('.list-group-item').text());
      $('#itemSubmit').text('保存')
      $('#itemDel').show();
    }
  });

  // 加载物料
  $('.list-group').on('click', '.list-group-item', function(event) {
    if (event.target.tagName === "A") {
      $(this).siblings().removeClass('active');
      $(this).addClass('active');
      var id = $(this).find('button').data('itemid');
      loadRank(id);
    }
  });

  function loadRank(itemID) {
    $.get('api/item/get/'+itemID, function(data) {
      var addContent = '';
      for (var i = 0; i < data.content.length; i++) {
        addContent += '\
          <div class="list-group">\
            <a href="#" class="list-group-item">\
              '+data.content[i].itemName+'\
              <span class="badge"><button class="glyphicon glyphicon-pencil" data-toggle="modal" data-target="#itemModal" data-itemid="'+data.content[i].itemID+'" data-rank="'+data.rank+'"></button></span>\
            </a>\
          </div>\
        ';
      }
      for (var i = parseInt(data.rank); i < 5; i++) {
        $('#rank'+i+' .list-group').html('');
      }
      $('#rank'+data.rank+' .list-group').html(addContent);
    });
  }
  loadRank(0);


  //编辑物料
  $('#itemSubmit').on('click', function() {
    var modal = $(this).parents('.modal');
    $(this).button('loading');
    $.post('api/item/edit', $('#itemForm').serialize(), function(data) {
      if (data.code === '') {
        modal.modal('hide');
        loadRank($('#itemParentID').val());
      } else {
        alert('data.message');
      }
    });
  });


});