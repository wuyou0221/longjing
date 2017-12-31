$(function($) {

  // 采购关联项目
  $('#purchaseDetailModal').on('click', '.item-single', function(event) {
    $('#purchaseProject').val($(this).text());
  });

});
