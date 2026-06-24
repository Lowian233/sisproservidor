<!-- Laravel App -->
<script type="text/javascript" src="{{ mix('/js/app.js') }}"></script>
{{-- Dependencias Package.json --}}
<script type="text/javascript" src="{{ mix('/js/dependencias.js') }}"></script>
{{-- Dependencias pdfmake --}}
<!-- DataTables -->
<script type="text/javascript" src="{{ mix('/js/datatable-depen.js') }}"></script>
{{-- plugins de datatables --}}
<script type="text/javascript" src="{{ mix('/js/datatable-plugins.js') }}"></script>
{{-- script para quitar el loader --}}
<script type="text/javascript">
	window.onload =function(){
		$('#contenedor_carga').css('opacity', '0');
		$('#contenido').fadeIn(2000);
		setTimeout(function(){
			$('#contenedor_carga').remove();
		}, 2000);
	}
</script>
<script>
  // 1) Mantén esta función porque tu código la llama en otros lados
  function recalcularwitdth() {
    try {
      if ($.fn.DataTable) {
        $('.dataTable').each(function() {
          if ($.fn.DataTable.isDataTable(this)) {
            $(this).DataTable().columns.adjust().draw(false);
          }
        });
      }
    } catch (e) {
      console.log('Error en recalcularwitdth:', e);
    }
  }

  // 2) Normaliza filas del tbody para evitar "mData undefined"
  function normalizarFilas($table) {
    var cols = $table.find('thead th').length;
    $table.find('tbody tr').each(function() {
      var $tr = $(this);
      // Si hay colspan/rowspan en tbody, DataTables se rompe: elimínalos o evita usarlos
      $tr.find('td[rowspan], td[colspan]').each(function() {
        // Lo más seguro: “aplanar” (quitar los span). Ajusta a tu caso si necesitas otra lógica.
        $(this).removeAttr('rowspan').removeAttr('colspan');
      });

      var tds = $tr.children('td');
      if (tds.length < cols) {
        // Completar celdas faltantes
        for (var i = tds.length; i < cols; i++) {
          $tr.append('<td></td>');
        }
      } else if (tds.length > cols) {
        // Recortar celdas sobrantes
        tds.slice(cols).remove();
      }
    });
  }

  // 3) Inicializador robusto
  function initDT(selector) {
    if (!$.fn.DataTable) return;
    var $t = $(selector);
    if ($t.length === 0) return;

    // Asegura que el elemento está en el DOM
    if (!document.body.contains($t[0])) return;

    // Debe existir thead con th
    if ($t.find('thead tr th').length === 0) {
      console.warn(`La tabla ${selector} no tiene <thead><th>…</th></thead>.`);
      return;
    }

    // Normaliza filas para evitar "mData" y errores por desalineación
    normalizarFilas($t);

    // Evita doble init
    if ($.fn.DataTable.isDataTable($t)) {
      $t.DataTable().destroy();
    }

    // Inicializa
    $t.DataTable({
      destroy: true,
      dom:
        "<'row'<'col-md-3'l><'col-md-5'B><'col-md-4'f>>" +
        "<'row'<'col-md-12'tr>>" +
        "<'row'<'col-md-6'i><'col-md-6'p>>",
      scrollX: true,
      autoWidth: false,
      colReorder: true,
      ordering: true,
      order: [0, 'desc'],
      searchHighlight: true,
      responsive: true,
      keys: true,
      lengthChange: true,
      searching: true,

      // Si alguna celda viniera vacía, evita errores con defaultContent
      columnDefs: [
        { targets: '_all', defaultContent: '' }
      ],

      buttons: [
        { extend: 'colvis', text: 'Columnas Visibles' },
        { extend: 'copy',   text: 'Copiar' },
        { extend: 'excel',  text: 'Excel' }
      ],
      language: {
        sProcessing: "Procesando...",
        sLengthMenu: "Mostrar _MENU_ registros",
        sZeroRecords: "No se encontraron resultados",
        sEmptyTable: "Ningún dato disponible en esta tabla",
        sInfo: "Mostrando _START_ a _END_ de _TOTAL_",
        sInfoEmpty: "Mostrando 0 a 0 de 0",
        sInfoFiltered: "",
        sSearch: "Buscar:",
        sLoadingRecords: "Cargando...",
        oPaginate: {
          sFirst: "Primero", sLast: "Último",
          sNext: "Siguiente", sPrevious: "Anterior"
        },
        oAria: {
          sSortAscending: ": Activar para ordenar ascendente",
          sSortDescending: ": Activar para ordenar descendente"
        }
      }
    });

    // Ajuste por si la tabla estaba en un contenedor oculto
    $t.on('draw.dt', function () {
      $t.DataTable().columns.adjust();
    });
  }

  // 4) Inicializa cuando el DOM está listo y visible
  $(document).ready(function() {
    // Pequeño delay por si hay renders diferidos
    setTimeout(function() {
      initDT('#reporteTable');
      initDT('#serviciosTable');
      // Ajuste general
      recalcularwitdth();
    }, 300);

    // Si usas tabs de Bootstrap, ajusta al mostrar
    $(document).on('shown.bs.tab shown.bs.collapse shown.bs.modal', function() {
      recalcularwitdth();
    });
  });
</script>
<script>
  $(document).ready(function () {
    console.log('Inicializando DataTables para reportes...');

    function initDT(selector) {
      if (!$.fn.DataTable) return;

      if ($(selector).length > 0 && $(`${selector} thead tr`).length > 0) {
        if ($.fn.DataTable.isDataTable(selector)) {
          console.log(`Destruyendo DataTable existente para ${selector}`);
        }
        $(selector).DataTable({
          destroy: true,
          dom:
            "<'row'<'col-md-3'l><'col-md-5'B><'col-md-4'f>>" +
            "<'row'<'col-md-12'tr>>" +
            "<'row'<'col-md-6'i><'col-md-6'p>>",
          scrollX: true,
          autoWidth: false,
          colReorder: true,
          ordering: true,
          order: [0, 'desc'],
          searchHighlight: true,
          responsive: true,
          keys: true,
          lengthChange: true,
          searching: true,
          buttons: [
            { extend: 'colvis', text: 'Columnas Visibles' },
            { extend: 'copy',   text: 'Copiar' },
            { extend: 'excel',  text: 'Excel' }
          ],
          language: {
            sProcessing:     "Procesando...",
            sLengthMenu:     "Mostrar _MENU_ registros",
            sZeroRecords:    "No se encontraron resultados",
            sEmptyTable:     "Ningún dato disponible en esta tabla",
            sInfo:           "Mostrando _START_ a _END_ de _TOTAL_",
            sInfoEmpty:      "Mostrando 0 a 0 de 0",
            sInfoFiltered:   "",
            sSearch:         "Buscar:",
            sLoadingRecords: "Cargando...",
            oPaginate: {
              sFirst: "Primero", sLast: "Último",
              sNext: "Siguiente", sPrevious: "Anterior"
            },
            oAria: {
              sSortAscending:  ": Activar para ordenar ascendente",
              sSortDescending: ": Activar para ordenar descendente"
            }
          }
        });
        console.log(`DataTable inicializado para ${selector}`);
      } else {
        console.log(`Tabla ${selector} sin <thead> o no existe`);
      }
    }

    // Espera breve para asegurar DOM listo
    setTimeout(function () {
      initDT('#reporteTable');
      initDT('#serviciosTable');

      // Ajuste de columnas por si hay tabs/ocultamiento
      if ($.fn.DataTable) {
        $('.dataTable').each(function () {
          if ($.fn.DataTable.isDataTable(this)) {
            $(this).DataTable().columns.adjust();
          }
        });
      }
		}, 300);
	});
</script>


@yield('NewScript')
