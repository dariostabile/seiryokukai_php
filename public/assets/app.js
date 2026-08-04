(function () {
  const dataTableLangUrl =
    (window.SeiryokukaiConfig && window.SeiryokukaiConfig.dataTableLangUrl)
    || '';

  const appUi = {
    showAlert(container, type, message) {
      if (!container) {
        return;
      }

      const safeType = type === 'success' ? 'success' : 'danger';
      container.className = 'alert alert-' + safeType;
      container.textContent = String(message || 'Operazione completata');
      container.classList.remove('d-none');
    },

    hideAlert(container) {
      if (!container) {
        return;
      }

      container.textContent = '';
      container.classList.add('d-none');
    },

    async postForm(url, form) {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
        body: new FormData(form),
      });

      const payload = await response.json().catch(function () {
        return {
          ok: false,
          message: 'Risposta non valida dal server',
        };
      });

      if (!response.ok || payload.ok !== true) {
        throw payload;
      }

      return payload;
    },
  };

  window.SeiryokukaiUi = appUi;

  const cards = document.querySelectorAll('.metric-card');
  cards.forEach((card, i) => {
    card.animate(
      [
        { transform: 'translateY(8px)', opacity: 0 },
        { transform: 'translateY(0)', opacity: 1 },
      ],
      { duration: 250 + i * 80, easing: 'ease-out', fill: 'both' }
    );
  });

  if (typeof DataTable !== 'undefined') {
    const tables = document.querySelectorAll('table.js-datatable');

    tables.forEach((table) => {
      if (table.dataset.serverSide === '1') {
        return;
      }

      // Keeps server-rendered order unless user explicitly sorts.
      let order = [];
      let columnDefs = [];
      if (table.id === 'pagamenti-table') {
        order = [[0, 'desc']];
        // Forza tipo colonna data pagamento come data europea
        columnDefs = [
          { type: 'date-eu', targets: 0 }
        ];
      }
      new DataTable(table, {
        language: {
          url: dataTableLangUrl,
        },
        order: order,
        pageLength: 10,
        columnDefs: columnDefs,
      });
    });
  }
  // Maschera input per data formato gg/mm/aaaa
  document.querySelectorAll('.js-date-ita').forEach(function(input) {
    input.addEventListener('input', function(e) {
      let v = input.value.replace(/[^0-9]/g, '');
      if (v.length > 8) v = v.slice(0,8);
      let out = '';
      if (v.length > 4) out = v.slice(0,2) + '/' + v.slice(2,4) + '/' + v.slice(4);
      else if (v.length > 2) out = v.slice(0,2) + '/' + v.slice(2);
      else out = v;
      input.value = out;
    });
    input.addEventListener('blur', function() {
      // Se la data non è valida, svuota il campo
      const val = input.value;
      if (val && !/^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
        input.value = '';
      }
    });
  });
})();
