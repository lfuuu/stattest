
$("input[type=checkbox].show_client_file_in_lk").on("change", function(event) {
  var obj = $(event.target);

  obj.prop({disabled: true});
  var isShow = obj.filter(":checked").length;

  $.get(
    "/contract/file-show-in-lk",
    {
      fileId: obj.data("id"),
      isShow: isShow
    },
    function (data) {
      if (data != "ok") {
        alert(data);
      } else {
        obj.removeProp("disabled");
      }
    }
  );
});