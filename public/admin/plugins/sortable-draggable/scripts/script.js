$(function() {
  // Comment
//   .accordion({
//     //header: "> div > h3",
//     header: function ( accordionElement ) {
//         return accordionElement.find( "span.accordi" );
//     },
//     event: "click",
//     active: false,
//     collapsible: true
//   }).
  $("#accordion").sortable({
    items: "> div",
    handle: "i.drag",
    revert: false,
    stop: function(e, ui) {
      var sectionList = $(this).sortable("toArray", { attribute: "data-section-id" });
      var sectionId = ui.item.context.dataset.sectionId;
      var index = ui.item.index();
      updateData({sectionId, sectionList});
      ui.item.children("h3").triggerHandler("focusout");
      $(this).accordion("refresh");
    }
  });

  $(".sortable").sortable({
    items: "> li",
    handle: ".draggable",
    revert: false,
    revertDuration: 50,
    placeholder: "ui-sortable-placeholder",
    sort: function(event, ui){ ui.item.addClass("selected"); },
    stop: function(event, ui){ ui.item.removeClass("selected"); },
    update: function(e, ui) {
      var questionList = $(this).sortable("toArray", { attribute: "data-item-id" });
      var sectionId = e.target.dataset.listId;
      var questionId = ui.item.context.dataset.itemId;
      var index = ui.item.index();
      updateData({sectionId, questionId, questionList});
    }
  });

  function updateData(obj) {
    let orderUrl = $("#form-order-url").val();
    if (orderUrl != '' || orderUrl != undefined) {
      var data = JSON.stringify(obj, null, 2);
      $.ajax({
        url: orderUrl,
        type: 'POST',
        data: obj,
        beforeSend: function () {
          $('#loader_section').show();
        },
        success: function (response) {
          $('#loader_section').hide();
          if (response.url) {
            //Pass only url/route for redirect inside this success()
            success(response.url);
          }
        },
        error: function (response) {
          if (response.responseJSON.code == 1) {
            fail();
          }
        },
      });
    }
  }

  $("#sortable-right").sortable({
    items: "> li",
    handle: ".draggable",
    revert: false,
    revertDuration: 50,
    helper: "clone",
    placeholder: "ui-sortable-placeholder",
    connectWith: ".connectedSortable"
  });

});
