
$(document).ready(function () {
  if (!$.fn.DataTable) {
    return;
  }

  var $tables = $('table[id="dataTable"], table.js-datatable, table[data-datatable]');

  $tables.each(function () {
    if ($.fn.DataTable.isDataTable(this)) {
      return;
    }

    $(this).DataTable({
      autoWidth: false,
      pageLength: 10,
      lengthMenu: [
        [5, 10, 25, 50, -1],
        [5, 10, 25, 50, 'Todos']
      ]
    });
  });
});
