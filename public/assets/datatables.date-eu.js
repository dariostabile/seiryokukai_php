/* DataTables plug-in for sorting European date format (dd/mm/yyyy or d/m/yyyy) */
// https://datatables.net/plug-ins/sorting/date-eu
jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
  "date-eu-pre": function (date) {
    if (!date) return 0;
    // Remove HTML tags and spaces
    var eu_date = date.replace(/<.*?>/g, '').replace(/\s+/g, '');
    if (eu_date === '') return 0;
    var dateParts = eu_date.split('/');
    // dd/mm/yyyy or d/m/yyyy
    if (dateParts.length !== 3) return 0;
    var day = dateParts[0].padStart(2, '0');
    var month = dateParts[1].padStart(2, '0');
    var year = dateParts[2].padStart(4, '0');
    return parseInt(year + month + day, 10);
  },
  "date-eu-asc": function (a, b) {
    return a - b;
  },
  "date-eu-desc": function (a, b) {
    return b - a;
  }
});
