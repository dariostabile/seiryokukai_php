(function () {
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
      new DataTable(table, {
        language: {
          url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/it-IT.json',
        },
        order: [],
        pageLength: 10,
      });
    });
  }
})();
