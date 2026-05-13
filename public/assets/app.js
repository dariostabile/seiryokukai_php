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
})();
