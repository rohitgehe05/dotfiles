/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

jQuery(document).ready(function($){
  var oTable = $('#datatables').dataTable(  {
        "sDom": 'T<"clear">lfrtip',
        "iDisplayLength": 50,
        "aaSorting": [[ 0, "desc" ]],
        "oTableTools": {
            "sSwfPath": optin_path + "/js/datatables/copy_csv_xls_pdf.swf",
            "aButtons": [
                "copy", "csv", "xls", 'pdf'
            ]
        }
    } )
});